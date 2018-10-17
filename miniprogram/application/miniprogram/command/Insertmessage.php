<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2017/9/5
 * Time: 14:36
 */

namespace app\miniprogram\command;

use app\miniapp\common\WXBizDataCrypt;
use app\miniprogram\model\MiniProgramsApplist;
use app\miniprogram\Model\MiniProgramsFollower;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use app\miniprogram\common\Controller;
use think\Db;
use think\image\Exception;
use think\Paginator;


/**该类用于轮询redis
 * 命令：php think test
 * Class InsertMessage
 * @package app\robot\controller
 */
class Insertmessage extends Command
{
	const QUEUE_SPIDER = 'spider';
	const QUEUE_SCENE = 'scene';
	const QUEUE_QRCODE = 'qrcode';

	protected function configure()
	{
		$this->setName('test')->setDescription('Here is the remark ');

	}

	protected function execute(Input $input, Output $output)
	{
		ini_set('default_socket_timeout', -1);
		Config::load('config.php');
		$redisconfig = Config::get('REDIS');
		$redis = new \Redis();
		$result = $redis->connect($redisconfig["REDIS_HOST"], $redisconfig["REDIS_PORT"]);

		$redisNFSconfig = Config::get('REDIS_NFS');
		if (empty($redisNFSconfig)) {
			$redisNFSconfig = $redisconfig;
		}

		$redis_nfs = new \Redis();
		$result_nfs = $redis_nfs->connect($redisNFSconfig["REDIS_HOST"], $redisNFSconfig["REDIS_PORT"]);

		if (!$result || !$result_nfs) {
			return;
		}

		error_reporting(E_ALL ^ E_NOTICE);
		$key = "mini_programs_data_message";
		$i = 0;
		do {
			$miniProgramsJson = $redis->blPop(array($key), 0);
			++$i;
			$date = date('Y-m-d H:i:s');
			echo "$i - blPop $date\n";
			if (empty($miniProgramsJson) || empty($miniProgramsJson[1])) {
				continue;
			}
			$object = json_decode($miniProgramsJson[1], true);

			if (empty($object) && count($object) <= 0) {
				continue;
			}


			//通过类型判断是xml（腾讯推送）还是json（小程序推送）
			$messageType = $object["type"];

			switch ($messageType) {
				case "json":

					$data = json_decode($object["dataJson"], true);
					if (empty($data) && count($data) <= 0) {
						continue;
					}
					//判断数据有效性
					$uniqueId = isset($data["uniqueId"]) ? $data["uniqueId"] : "";

					$applist = "SELECT * FROM `mini_programs_applist` WHERE uniqueid= :uniqueId";
					$applistModel = new MiniProgramsApplist();
					$applistInfo = $applistModel->getAppByUniqueId($uniqueId);
					//$applistInfo = $applistModel->db()->query($applist, ['uniqueId' => $uniqueId]);
					if (empty($applistInfo) || count($applistInfo) <= 0) {
						echo "$i - APP is empty \n";
						continue;
					}


					$phoneData = $data["phoneData"];
					$userData = $data["userData"];

					if (empty($phoneData)) {
						echo "$i - phoneData is empty \n";
						continue;
					}
					if (empty($userData)) {
						echo "$i - userData is empty \n";
						continue;
					}

					$userData["nickName"] = substr($userData["nickName"], 0, 255);


					if (empty($data['IP'])) {//若当前数据没有上传ip则php后端获取Ip
						$phoneData["IP"] = $object["IP"];
					} else {
						$phoneData["IP"] = $data["IP"];
					}

					$appId = empty($applistInfo[0]["id"]) ? 0 : $applistInfo[0]["id"];
					$openid_share = '';
					$new_follower = false;
					$follower_id = 0;
					$curUserKey = isset($userData["curUserKey"]) ? trim($userData["curUserKey"]) : "";
					$openId = isset($userData["openId"]) ? trim($userData["openId"]) : "";

					if (empty($curUserKey)) {
						echo "$i - curUserKey is empty \n";
						continue;
					}

					if ((!empty($data["duration_detail"])) && ($data["type"] == "duration") && ($data["type"] != "touchmove")) {
						$area = empty($data["duration_detail"]["area"]) ? "" : $data["duration_detail"]["area"];
						$event_desc = empty($data["duration_detail"]["event_desc"]) ? "" : $data["duration_detail"]["event_desc"];
						$duration = empty($data["duration_detail"]["duration"]) ? "" : $data["duration_detail"]["duration"];
						$event_type = empty($data["type"]) ? "" : $data["type"];
					} else {
						$component = empty($data["event_detail"]["component"]) ? "" : $data["event_detail"]["component"];
						$touches = empty($data["event_detail"]["touches"]) ? "" : $data["event_detail"]["touches"];
						$event_desc = "";
						$area = "";
						$duration = "";
						$event_type = empty($data["type"]) ? "" : $data["type"];


						// https://github.com/catalyst8/Panmeta_Issues/issues/1219
						$openid_share = $event_detail = isset($data['pageUrlOption']['openId']) ? trim($data['pageUrlOption']['openId']) : '';
						$curuserkey_share = isset($data['pageUrlOption']['curUserKey']) ? trim($data['pageUrlOption']['curUserKey']) : '';


						if ($openid_share == 'undefined') {
							$openid_share = '';
						}
						// if (strpos($event_type, 'pageOnShareFrom') !== false) {
						// 	$arr = explode(" ", $event_type);
						// 	$event_type = $arr[0];
						// 	$openid_share = $event_detail = $arr[1];
						// 	$data["type"] = $arr[0];
						// }

					}
					$defaultAvatarUrl = "/static/images/no_avatar.png";
					$curPageUrl = empty($data["pageUrl"]) ? "" : $data["pageUrl"];

					$miniFollowerModel = new MiniProgramsFollower();
					$again = 0;
					AGAIN:
					if ($again >= 2) {
						echo "$i - AGAIN fail appId=$appId curUserKey=$curUserKey openId=$openId\n";
						continue;
					}
					if (empty($openId)) {
						$info = $miniFollowerModel->getFollowerByUserKey($curUserKey, $appId);
					} else {
						$sql = "select * from mini_programs_follower where openid = :openid and appid = :appid ORDER by id asc limit 1; ";
						$info = $miniFollowerModel->query($sql, ['openid' => $openId, 'appid' => $appId]);
						if(empty($info) && count($info) <= 0){
							$info = $miniFollowerModel->getFollowerByUserKey($curUserKey, $appId);
						}
					}


					if (empty($info) && count($info) <= 0) {
						$followerInfo = $this->getFollowerInfoArr($phoneData, $userData, $defaultAvatarUrl, $openId, $uniqueId, $appId, $curUserKey);

						// 解决并发问题
						$followerid = $miniFollowerModel->name("mini_programs_follower")->insertGetId($followerInfo);
						if ($followerid <= 0) {
							++$again;
							echo "$i - AGAIN appId=$appId curUserKey=$curUserKey openId=$openId\n";
							goto AGAIN;
						}
						$new_follower = true;
					} else {
						//更新完后返回影响的条数，若返回为0，则没修改任何数据
						$followerInfoUpdate = $this->getFollowerInfoArr($phoneData, $userData, $defaultAvatarUrl, $openId, $uniqueId, $appId, $curUserKey);
						$updateResult = $miniFollowerModel->db()->where("id", $info[0]["id"])->update($followerInfoUpdate);
						$followerid = $info[0]["id"];

						//当前事件可以获取到手机号
						if ($event_type == "getPhoneNumber") {
							$phoneNumberDetail = empty($data["detail"]) ? "" : $data["detail"];
							$encryptedData = empty($phoneNumberDetail["encryptedData"]) ? "" : $phoneNumberDetail["encryptedData"];
							$iv = empty($phoneNumberDetail["iv"]) ? "" : $phoneNumberDetail["iv"];

							$pc = new WXBizDataCrypt($applistInfo[0]["appid"], $info[0]["sessionKey"]);
							$errCode = $pc->decryptData($encryptedData, $iv, $dataPhoneNumber);
							if ($errCode == 0) {
								$phoneNumobject = json_decode($dataPhoneNumber, true);
								$phoneNumber = $phoneNumobject["phoneNumber"];
								$miniFollowerModel->db()->where("id", $info[0]["id"])->update(["phoneNumber" => $phoneNumber]);
							}
						}
					}

					$follower_id = $followerid;
					$follower_id_share = 0;

					$again_share = 0;
					AGAIN_SHARE:
					if ($again_share >= 2) {
						echo "$i - AGAIN_SHARE fail appId=$appId curUserKey=$curuserkey_share openId=$openid_share \n";
						continue;
					}

					$info = [];
					if( !empty($openid_share) ) {
						$sql = "select * from mini_programs_follower where openid = :openid and appid = :appid ORDER by id asc limit 1; ";
						$info = $miniFollowerModel->query($sql, ['openid' => $openid_share, 'appid' => $appId]);
					} else if (!empty($curuserkey_share)) {
						$info = $miniFollowerModel->getFollowerByUserKey($curuserkey_share, $appId);
					}

					if( isset($info[0]['id']) ) {
						$follower_id_share = $info[0]["id"];
					} else if( !empty($curuserkey_share) || !empty($openid_share)  ) {
						if (empty($info) && count($info) <= 0) {
							$followerInfo = [
								"openid" => $openid_share,
								"followTime" => time(),
								"appid" => $appId,
								"curUserKey" => $curuserkey_share,
							];

							$follower_id_share = $miniFollowerModel->name("mini_programs_follower")->insertGetId($followerInfo);
							if ($follower_id_share <= 0) {
								++$again_share;
								echo "$i - AGAIN_SHARE appId=$appId curUserKey=$curuserkey_share openId=$openid_share \n";
								goto AGAIN_SHARE;
							}
							$new_follower = true;
						} else {
							$follower_id_share = $info[0]["id"];
						}
					}

					$followerLocation = $this->getFollowerLocationInfoArr($userData, $defaultAvatarUrl, $appId, $followerid, $openId, $uniqueId, $openId);


					$miniFollowerLocationModel = new \app\miniapp\model\MiniProgramsFollowerLocation();
					$latitude = empty($followerLocation["latitude"]) ? 0 : $followerLocation["latitude"];
					$longitude = empty($followerLocation["longitude"]) ? 0 : $followerLocation["longitude"];
					$openid = empty($followerLocation["openid"]) ? "" : $followerLocation["openid"];


					//当经纬度获取失败（包括用户拒绝获取经纬度），则通过IP转换经纬度，并标记标识

					if ($latitude == 0 && $longitude == 0) {

						$locationArr = $this->getLocation($phoneData["IP"]);

						if (empty($locationArr) || empty($locationArr[0]['latitude']) || empty($locationArr[0]['longitude'])) {
							$latitude = 0;
							$longitude = 0;
						} else {
							$latitude = $locationArr[0]['latitude'];
							$longitude = $locationArr[0]['longitude'];
						}
						$type = "IP";
					} else {
						$type = "mobileGps";
					}
					$followerLocation['latitude'] = $latitude;
					$followerLocation['longitude'] = $longitude;
					$followerLocation['type'] = $type;


					$sql = "select * from mini_programs_follower_location where (latitude = :latitude and  longitude = :longitude) and  appid = :appid and  followerid = :followerid ; ";
					$info = $miniFollowerLocationModel->query($sql, ['latitude' => $latitude, 'longitude' => $longitude, 'appid' => $appId, 'followerid' => $followerid]);
					if (empty($info) && count($info) <= 0) {
						$id = $miniFollowerLocationModel->name("mini_programs_follower_location")->insertGetId($followerLocation);
						if ($id <= 0) {
							continue;
						}
					}

					$miniFollowerMobileInfoModel = new \app\miniapp\model\MiniProgramsFollowerMobileInfo();
					$miniFollowerMobileNetWorkTypeModel = new \app\miniapp\model\MiniProgramsFollowerMobileNetworkType();

					$model = empty($phoneData["model"]) ? "" : $phoneData["model"];
					$networkType = empty($phoneData["networkType"]) ? "" : $phoneData["networkType"];


					$sqlModel = "select * from mini_programs_follower_mobile_info where model = :model   and  appid = :appid and  followerid = :followerid ; ";

					$infoModel = $miniFollowerMobileInfoModel->query($sqlModel, ['model' => $model, 'appid' => $appId, 'followerid' => $followerid]);

					if (empty($infoModel) && count($infoModel) <= 0) {
						$mobileInfo = $this->getFollowerMobileInfoArr($phoneData, $userData, $defaultAvatarUrl, $appId, $followerid, $openId, $uniqueId);
						$mombileid = $miniFollowerMobileInfoModel->name("mini_programs_follower_mobile_info")->insertGetId($mobileInfo);

					} else {
						$mombileid = $infoModel[0]["id"];
					}

					$sqlNetworkType = "select * from mini_programs_follower_networktype_info where  networkType = :networkType   and  mobileid = :mobileid   and  appid = :appid and  followerid = :followerid ;  ";
					$infoNetworkType = $miniFollowerMobileNetWorkTypeModel->query($sqlNetworkType, ['networkType' => $networkType, 'mobileid' => $mombileid, 'appid' => $appId, "followerid" => $followerid]);

					if (empty($infoNetworkType) && count($infoNetworkType) <= 0) {
						$networkInfo = $this->getFollowerMobileNetworInfoArr($defaultAvatarUrl, $phoneData, $userData, $uniqueId, $appId, $followerid, $mombileid, $openId);

						$netWorkId = $miniFollowerMobileNetWorkTypeModel->name("mini_programs_follower_networktype_info")->insertGetId($networkInfo);
					}

					//将消息插入message表中,chattype为3(小程序event事件)
					$messageMode = new \app\miniapp\model\MiniProgramsMessage();

					if (empty($data["userData"]["avatarUrl"])) {
						$data["userData"]["avatarUrl"] = $defaultAvatarUrl;
						$object["dataJson"] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
					}
					$message = [
						"openid" => empty($userData["openId"]) ? "" : $userData["openId"],
						"appid" => empty($applistInfo[0]["id"]) ? 0 : $applistInfo[0]["id"],
						"uniqueid" => empty($applistInfo[0]["uniqueid"]) ? "" : $applistInfo[0]["uniqueid"],
						"followerid" => empty($followerid) ? 0 : $followerid,
						"time" => time(),
						"type" => empty($data["type"]) ? "" : $data["type"],
						"param" => empty($object["dataJson"]) ? "" : $object["dataJson"],
						"detail" => empty($event_detail) ? "" : $event_detail,
						"chattype" => 3
					];

					$messageId = $messageMode->name("mini_programs_message")->insertGetId($message);
					//将消息插入messagenewrecord表中(查找某个app下的某个用户信息，若存在则更新，否则插入)
					$messageNewRecordMode = new \app\miniapp\model\MiniProgramsNewMessageRecord();
					$sql = "select * from mini_programs_newmessage_record where followerid = :followerid and appid = :appid ";

					$newMessageInfo = $messageNewRecordMode->query($sql, ['followerid' => $followerid, "appid" => $appId]);

					if (empty($newMessageInfo) && count($newMessageInfo) <= 0) {

						$newMessage = [
							"openid" => empty($userData["openId"]) ? "" : $userData["openId"],
							"appid" => $appId,
							"uniqueid" => empty($applistInfo[0]["uniqueid"]) ? "" : $applistInfo[0]["uniqueid"],
							"followerid" => empty($followerid) ? 0 : $followerid,
							"messageid" => $messageId,
							"time" => time()
						];
						$newMessageid = $messageNewRecordMode->name("mini_programs_newmessage_record")->insertGetId($newMessage);

						//若无法插入数据则返回0,则更新该条记录，解决并发问题
						if ($newMessageid == 0) {
							$messageNewRecordMode->db()->where("followerid", $followerid)->update(
								[
									"openid" => empty($userData["openId"]) ? "" : $userData["openId"],
									"messageid" => $messageId,
									"time" => time()
								]
							);
						}
					} else {

						$messageNewRecordMode->db()->where("id", $newMessageInfo[0]["id"])->update(
							[
								"openid" => empty($userData["openId"]) ? "" : $userData["openId"],
								"messageid" => $messageId,
								"time" => time()
							]
						);
						$newMessageid = $newMessageInfo[0]["id"];
					}

					$eventInfo = $this->getEventInfoArr($data, $event_desc, $event_type, $component, $touches, $area, $duration, $phoneData, $userData, $defaultAvatarUrl, $openId, $uniqueId,
						$appId, $followerid, $messageId, $object, $event_detail);

					$eventModeEvent = new \app\miniapp\model\MiniProgramsEvent();
					$eventModeEvent->name("mini_programs_event")->insert($eventInfo);

					$data['app_id'] = $appId;

					//统计scene
					$flag = [];
					if (!empty($data["scene"]) && $data["scene"] != 0) {
						$flag['follower_id'] = $follower_id;
						$flag['new_follower'] = $new_follower;
						$flag["appid"] = $appId;
						$flag["scene"] = $data["scene"];
						$flag["pageUrl"] = $curPageUrl;
						// $this->sceneAnalytics($flag);

						$scene_data = json_encode($flag, JSON_UNESCAPED_UNICODE);
						$redis_nfs->rpush(self::QUEUE_SCENE, $scene_data);
					}
					// 统计 小程序二维码 小程序码 扫描记录
					if (in_array($data["scene"], [1011, 1047])) {
						$data['follower_id'] = $follower_id;
						$data['new_follower'] = $new_follower;
						// $this->QrcodeScan($data);

						$qrcode_data = json_encode($data, JSON_UNESCAPED_UNICODE);
						$redis_nfs->rpush(self::QUEUE_QRCODE, $qrcode_data);
					}

					// 蜘蛛图数据搜集
					if (in_array($data["type"], ['pageOnLoad', 'pageOnShow', 'pageOnShareFrom'])) {
						$data['follower_id_share'] = $follower_id_share;
						$data['userData']['latitude'] = $latitude;
						$data['userData']['longitude'] = $longitude;
						$data['userData']['uniqueid'] = $applistInfo[0]["uniqueid"];
						$data['userData']['appid'] = $appId;
						$data['userData']['followerid'] = $follower_id;

						$spider_data = json_encode($data, JSON_UNESCAPED_UNICODE);
						$redis_nfs->rpush(self::QUEUE_SPIDER, $spider_data);
					}

					break;
				case "xml":
					//解密
					$xml_tree = new \DOMDocument();
					$xml_tree->loadXML($object["xml"]);
					$array_e = $xml_tree->getElementsByTagName('Encrypt');
					$encrypt = $array_e->item(0)->nodeValue;

					//获取原始Id
					$array_userName = $xml_tree->getElementsByTagName('ToUserName');
					$originalid = $array_userName->item(0)->nodeValue;

					$applist = "SELECT * FROM `mini_programs_applist` WHERE originalid= :originalid";
					$applistModel = new \app\miniapp\model\MiniProgramsApplist();
					$applistInfo = $applistModel->db()->query($applist, ['originalid' => $originalid]);

					//当前数据库无该app
					if (empty($applistInfo) || count($applistInfo) <= 0 || empty($applistInfo[0])) {
						continue;
					}
					$uniqueid = $applistInfo[0]["uniqueid"];
					$appid = $applistInfo[0]["id"];


					$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
					$from_xml = sprintf($format, $encrypt);
					$pc = new \app\miniapp\common\WXBizMsgCrypt($applistInfo[0]["token"], $applistInfo[0]["encodingaeskey"], $applistInfo[0]["appid"]);
					$msg = '';
					$sha1 = new \app\miniapp\common\sha1();
					$xmlparse = new \app\miniapp\common\xmlparse;
					$array = $xmlparse->extract($from_xml);


					$encrypt = $array[1];
					$msg_sign = $sha1->getSHA1($applistInfo[0]["token"], $object["timestamp"], $object["nonce"], $encrypt);

					$errCode = $pc->decryptMsg($msg_sign[1], $object["timestamp"], $object["nonce"], $from_xml, $msg);

					if ($errCode == 0) {

						if (!empty($msg)) {
							//解析xml
							$xml = simplexml_load_string($msg);
							$type = strval($xml->MsgType);
							$toUserName = strval($xml->ToUserName);
							$fromUserName = strval($xml->FromUserName);
							$createTime = intval($xml->CreateTime);


							$applist = "SELECT * FROM `mini_programs_follower` WHERE openid= :openid";
							$followerModel = new \app\miniapp\model\MiniProgramsFollower();
							$followerInfo = $followerModel->db()->query($applist, ['openid' => $fromUserName]);

							//当前数据库中不存在这个人,则插入信息用户数据(有程序合并当前用户信息)
							if (empty($followerInfo)) {
								$defaultAvatarUrl = "/static/images/no_avatar.png";
								$followerInfo = $this->getFollowerInfoArr("", "", $defaultAvatarUrl, $fromUserName, $uniqueid, $appid, "");


								$followerid = $followerModel->name("mini_programs_follower")->insertGetId($followerInfo);
								$followerInfo[0]["openid"] = $fromUserName;
								$followerInfo[0]["id"] = $followerid;
							}

							switch ($type) {
								//文本
								case "text":
									$content = strval($xml->Content);
									$msgId = intval($xml->MsgId);
									$param = [
										"toUserName" => $toUserName,
										"fromUserName" => $fromUserName,
										"createTime" => $createTime,
										"type" => $type,
										"content" => $content,
										"msgId" => $msgId
									];
									break;
								case "image":
									$picUrl = strval($xml->PicUrl);
									$mediaId = intval($xml->MediaId);
									$msgId = intval($xml->MsgId);
									$param = [
										"toUserName" => $toUserName,
										"fromUserName" => $fromUserName,
										"createTime" => $createTime,
										"type" => $type,
										"mediaId" => $mediaId,
										"msgId" => $msgId,
										"picUrl" => $picUrl
									];
									break;
								case "miniprogrampage":
									$msgId = intval($xml->MsgId);
									$title = intval($xml->Title);
									$appId = intval($xml->AppId);
									$pagePath = strval($xml->PagePath);
									$thumbUrl = strval($xml->ThumbUrl);
									$thumbMediaId = intval($xml->ThumbMediaId);
									$param = [
										"toUserName" => $toUserName,
										"fromUserName" => $fromUserName,
										"createTime" => $createTime,
										"type" => $type,
										"msgId" => $msgId,
										"title" => $title,
										"appId" => $appId,
										"pagePath" => urldecode($pagePath),
										"thumbUrl" => $thumbUrl,
										"thumbMediaId" => $thumbMediaId
									];
									break;
								case "event":
									$event = strval($xml->Event);
									$sessionfrom = strval($xml->SessionFrom);
									$param = [
										"toUserName" => $toUserName,
										"fromUserName" => $fromUserName,
										"createTime" => $createTime,
										"type" => $type,
										"event" => $event,
										"sessionfrom" => $sessionfrom,
									];
									break;
							}

							//将消息插入message表中,chattype为3(小程序event事件)
							$messageMode = new \app\miniapp\model\MiniProgramsMessage();
							$paramJson = json_encode(empty($param) ? "" : $param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
							$message = [
								"openid" => empty($followerInfo[0]["openid"]) ? "" : $followerInfo[0]["openid"],
								"appid" => $appid,
								"uniqueid" => $uniqueid,
								"followerid" => empty($followerInfo[0]["id"]) ? 0 : $followerInfo[0]["id"],
								"time" => time(),
								"type" => empty($type) ? "" : $type,
								"param" => $paramJson,
								"chattype" => 0
							];
							if ($type == "text") {
								$content = $xml->Content;
								$message["detail"] = $content;
							} else {
								$message["detail"] = "";
							}
							$messageId = $messageMode->name("mini_programs_message")->insertGetId($message);


							//将消息插入messagenewrecord表中(查找某个app下的某个用户信息，若存在则更新，否则插入)
							$messageNewRecordMode = new \app\miniapp\model\MiniProgramsNewMessageRecord();
							$sql = "select * from mini_programs_newmessage_record where followerid = :followerid and appid = :appid ";

							$newMessageInfo = $messageNewRecordMode->query($sql, ['followerid' => $followerInfo[0]["id"], "appid" => $appid]);

							if (empty($newMessageInfo) && count($newMessageInfo) <= 0) {

								$newMessage = [
									"openid" => empty($followerInfo[0]["openid"]) ? "" : $followerInfo[0]["openid"],
									"appid" => $appid,
									"uniqueid" => $uniqueid,
									"followerid" => empty($followerInfo[0]["id"]) ? 0 : $followerInfo[0]["id"],
									"messageid" => $messageId,
									"time" => time()
								];
								$newMessageid = $messageNewRecordMode->name("mini_programs_newmessage_record")->insertGetId($newMessage);
								//若无法插入数据则返回0,则更新该条记录，解决并发问题
								if ($newMessageid == 0) {
									$messageNewRecordMode->db()->where("followerid", $followerInfo[0]["id"])->update(
										[
											"openid" => empty($followerInfo[0]["openid"]) ? "" : $followerInfo[0]["openid"],
											"messageid" => $messageId,
											"time" => time()
										]
									);
								}

							} else {

								$messageNewRecordMode->db()->where("id", $newMessageInfo[0]["id"])->update(
									[
										"openid" => empty($followerInfo[0]["openid"]) ? "" : $followerInfo[0]["openid"],
										"messageid" => $messageId,
										"time" => time()
									]
								);
							}
							$followerId = empty($followerInfo[0]["id"]) ? 0 : $followerInfo[0]["id"];


							//0为收消息
							$localMsgType = 0;
							$messageModel = new \app\miniapp\model\MiniProgramsMessage();
							$chatModel = new \app\miniapp\model\MiniProgramsChatMessage();
							//将信息插入，mini_programs_chat_message表
							switch ($type) {
								//文本
								case "text":
									$content = $xml->Content;
									$msgId = $xml->MsgId;
									$data = [
										"createtime" => empty($createTime) ? 0 : $createTime,
										"msgtype" => trim((string)empty($type) ? "" : $type),
										"content" => trim((string)empty($content) ? "" : $content),
										"msgid" => empty($msgId) ? 0 : $msgId,
										"fromusername" => trim((string)empty($fromUserName) ? "" : $fromUserName),
										"tousername" => trim((string)empty($toUserName) ? "" : $toUserName),
										"localmsgtype" => empty($localMsgType) ? 0 : $localMsgType,
										"followerid" => $followerId,
										"uniqueid" => $uniqueid,
										"messageid" => $messageId
									];
									$chatModel->name("mini_programs_chat_message")->insert($data);

									break;
								//图片
								case "image":
									$picUrl = $xml->PicUrl;
									$mediaId = $xml->MediaId;
									$msgId = $xml->MsgId;
									$data = [
										"createtime" => empty($createTime) ? 0 : $createTime,
										"msgtype" => trim((string)empty($type) ? "" : $type),
										"picurl" => trim((string)empty($picUrl) ? "" : $picUrl),
										"mediaid" => trim((string)empty($mediaId) ? "" : $mediaId),
										"msgid" => empty($msgId) ? 0 : $msgId,
										"fromusername" => trim((string)empty($fromUserName) ? "" : $fromUserName),
										"tousername" => trim((string)empty($toUserName) ? "" : $toUserName),
										"localmsgtype" => empty($localMsgType) ? 0 : $localMsgType,
										"followerid" => $followerId,
										"uniqueid" => $uniqueid,
										"messageid" => $messageId
									];
									$chatModel->name("mini_programs_chat_message")->insert($data);


									break;
								//小程序
								case "miniprogrampage":
									$msgId = $xml->MsgId;
									$title = $xml->Title;
									$appId = $xml->AppId;
									$pagePath = $xml->PagePath;
									$thumbUrl = $xml->ThumbUrl;
									$thumbMediaId = $xml->ThumbMediaId;
									$data = [
										"createtime" => empty($createTime) ? 0 : $createTime,
										"msgtype" => trim((string)empty($type) ? "" : $type),
										"title" => trim((string)empty($title) ? "" : $title),
										"appid" => trim((string)empty($appId) ? "" : $appId),
										"pagepath" => urldecode(trim((string)empty($pagePath) ? "" : $pagePath)),
										"thumburl" => trim((string)empty($thumbUrl) ? "" : $thumbUrl),
										"thumbmediaid" => trim((string)empty($thumbMediaId) ? "" : $thumbMediaId),
										"msgid" => empty($msgId) ? 0 : $msgId,
										"fromusername" => trim((string)empty($fromUserName) ? "" : $fromUserName),
										"tousername" => trim((string)empty($toUserName) ? "" : $toUserName),
										"localmsgtype" => empty($localMsgType) ? 0 : $localMsgType,
										"followerid" => $followerId,
										"uniqueid" => $uniqueid,
										"messageid" => $messageId
									];

									$chatModel->name("mini_programs_chat_message")->insert($data);


									break;
								//事件
								case "event":
									$event = $xml->Event;
									$sessionfrom = $xml->SessionFrom;

									$data = [
										"createtime" => empty($createTime) ? 0 : $createTime,
										"msgtype" => trim((string)empty($type) ? "" : $type),
										"event" => trim((string)empty($event) ? "" : $event),
										"sessionfrom" => trim((string)empty($sessionfrom) ? "" : $sessionfrom),
										"fromusername" => trim((string)empty($fromUserName) ? "" : $fromUserName),
										"tousername" => trim((string)empty($toUserName) ? "" : $toUserName),
										"localmsgtype" => empty($localMsgType) ? 0 : $localMsgType,
										"followerid" => $followerId,
										"uniqueid" => $uniqueid,
										"messageid" => $messageId
									];
									$chatModel->name("mini_programs_chat_message")->insert($data);


									break;
							}

							//更新接收到用户最后消息的时间
							$followerModel->db()->where('id', $followerId)->update(array(
									'lastMsgTime' => time(),
									'sendMsgNum' => 0)
							);
						}
					} else {
						print($errCode . "\n");
					}
					continue;
			}

			echo "$i - Done\n";
		} while (true);

	}

	/**
	 * 通过IP获取经纬度
	 * @param $ip
	 * @return null
	 */
	public function getLocation($ip)
	{
		if (($ip_address_long = sprintf('%u', ip2long(trim($ip))))) {
			$SQL = 'SELECT ip.`latitude`,ip.`longitude`, cn.`country_name`,cn.`subdivision_1_name`, cn.`subdivision_2_name`, cn.`city_name`,'
				. ' en.`country_name` as `en_country_name`, en.`subdivision_1_name` as `en_subdivision_1_name`, en.`subdivision_2_name` as `en_subdivision_2_name`, en.`city_name` as `en_city_name`'
				. ' FROM `' . 'w_GeoLite2-City-Blocks-IPv4' . '` ip'
				. ' LEFT JOIN `' . 'w_GeoLite2-City-Locations' . '` cn ON cn.`geoname_id`=ip.`geoname_id`'
				. ' LEFT JOIN `' . 'w_GeoLite2-City-Locations_en' . '` en ON en.`geoname_id`=ip.`geoname_id`'
				. ' WHERE ' . ':ip_address_long' . ' BETWEEN ip.`start` AND ip.`end` ORDER BY ip.`start` DESC LIMIT 1';


			Config::load('config.php');
			$geoliteConfig = Config::get('GEOLITE');

			$query = Db::connect([
				// 数据库类型
				'type' => $geoliteConfig['type'],
				// 服务器地址
				'hostname' => $geoliteConfig['hostname'],
				// 数据库名
				'database' => $geoliteConfig['database'],
				// 用户名
				'username' => $geoliteConfig['username'],
				// 密码
				'password' => $geoliteConfig['password'],
				// 端口
				'hostport' => '',
				// 连接dsn
				'dsn' => '',
				// 数据库连接参数
				'params' => [],
				// 数据库编码默认采用utf8
				'charset' => $geoliteConfig['charset'],
				// 开启断线重连
				'break_reconnect' => $geoliteConfig['break_reconnect'],
			]);

			$qlist = $query->query($SQL, ['ip_address_long' => $ip_address_long]);


			if (count($qlist) > 0) {
				return $qlist;
			}
		}
		return null;
	}


	/**
	 * 拼接用户信息数组
	 */
	private function getFollowerInfoArr($phoneData, $userData, $defaultAvatarUrl, $openId, $uniqueId, $appId, $curUserKey)
	{

		if (!empty($phoneData["model"])) {
			$followerInfo["model"] = $phoneData["model"];
		}
		if (!empty($phoneData["pixelRatio"])) {
			$followerInfo["pixelRatio"] = $phoneData["pixelRatio"];
		}
		if (!empty($phoneData["windowWidth"])) {
			$followerInfo["windowWidth"] = $phoneData["windowWidth"];
		}
		if (!empty($phoneData["windowHeight"])) {
			$followerInfo["windowHeight"] = $phoneData["windowHeight"];
		}
		if (!empty($phoneData["system"])) {
			$followerInfo["system"] = $phoneData["system"];
		}
		if (!empty($phoneData["language"])) {
			$followerInfo["language_sys"] = $phoneData["language"];
		}
		if (!empty($phoneData["screenWidth"])) {
			$followerInfo["screenWidth"] = $phoneData["screenWidth"];
		}
		if (!empty($phoneData["screenHeight"])) {
			$followerInfo["screenHeight"] = $phoneData["screenHeight"];
		}
		if (!empty($phoneData["brand"])) {
			$followerInfo["brand"] = $phoneData["brand"];
		}
		if (!empty($phoneData["fontSizeSetting"])) {
			$followerInfo["fontSizeSetting"] = $phoneData["fontSizeSetting"];
		}
		if (!empty($phoneData["platform"])) {
			$followerInfo["platform"] = $phoneData["platform"];
		}
		if (!empty($phoneData["SDKVersion"])) {
			$followerInfo["SDKVersion"] = $phoneData["SDKVersion"];
		}
		if (!empty($phoneData["projectname"])) {
			$followerInfo["projectname"] = $phoneData["projectname"];
		}
		if (!empty($phoneData["wechatVersion"])) {
			$followerInfo["wechatVersion"] = $phoneData["wechatVersion"];
		}
		if (!empty($phoneData["networkType"])) {
			$followerInfo["networkType"] = $phoneData["networkType"];
		}
		if (!empty($phoneData["IP"])) {
			$followerInfo["IP"] = $phoneData["IP"];
		}
		if (!empty($openId)) {
			$followerInfo["openid"] = $openId;
		}
		if (!empty($userData["avatarUrl"])) {
			$followerInfo["avatarUrl"] = $userData["avatarUrl"];
		} else {
			$followerInfo["avatarUrl"] = $defaultAvatarUrl;
		}
		if (!empty($userData["city"])) {
			$followerInfo["city"] = $userData["city"];
		}
		if (!empty($userData["country"])) {
			$followerInfo["country"] = $userData["country"];
		}
		if (!empty($userData["gender"])) {
			$followerInfo["gender"] = $userData["gender"];
		}
		if (!empty($userData["language"])) {
			$followerInfo["language_user"] = $userData["language"];
		}
		if (!empty($userData["nickName"])) {
			$followerInfo["nickName"] = $userData["nickName"];
		}
		if (!empty($userData["province"])) {
			$followerInfo["province"] = $userData["province"];
		}
		if (!empty($userData["latitude"])) {
			$followerInfo["latitude"] = $userData["latitude"];
		}
		if (!empty($userData["longitude"])) {
			$followerInfo["longitude"] = $userData["longitude"];
		}
		if (!empty($userData["unionid"])) {
			$followerInfo["unionId"] = $userData["unionid"];
		}
		if (!empty($uniqueId)) {
			$followerInfo["uniqueid"] = $uniqueId;
		}
		$followerInfo["followTime"] = time();
		if (!empty($appId)) {
			$followerInfo["appid"] = $appId;
		}
		if (!empty($userData["sessionKey"])) {
			$followerInfo["sessionKey"] = $userData["sessionKey"];
		}
		if (!empty($curUserKey)) {
			$followerInfo["curUserKey"] = $curUserKey;
		}
		return $followerInfo;

	}


	/**
	 * 拼接用户地理位置信息数组
	 */
	private function getFollowerLocationInfoArr($userData, $defaultAvatarUrl, $appId, $followerid, $openId, $uniqueId, $openId)
	{
		if (!empty($openId)) {
			$followerLocation["openid"] = $openId;
		}
		if (!empty($userData["avatarUrl"])) {
			$followerLocation["avatarUrl"] = $userData["avatarUrl"];
		} else {
			$followerLocation["avatarUrl"] = $defaultAvatarUrl;
		}
		if (!empty($userData["city"])) {
			$followerLocation["city"] = $userData["city"];
		}
		if (!empty($userData["country"])) {
			$followerLocation["country"] = $userData["country"];
		}
		if (!empty($userData["gender"])) {
			$followerLocation["gender"] = $userData["gender"];
		}
		if (!empty($userData["language"])) {
			$followerLocation["language_user"] = $userData["language"];
		}
		if (!empty($userData["nickName"])) {
			$followerLocation["nickName"] = $userData["nickName"];
		}
		if (!empty($userData["province"])) {
			$followerLocation["province"] = $userData["province"];
		}
		if (!empty($userData["latitude"])) {
			$followerLocation["latitude"] = $userData["latitude"];
		}
		if (!empty($userData["longitude"])) {
			$followerLocation["longitude"] = $userData["longitude"];
		}
		if (!empty($uniqueId)) {
			$followerLocation["uniqueid"] = $uniqueId;
		}
		if (!empty($appId)) {
			$followerLocation["appid"] = $appId;
		}
		if (!empty($followerid)) {
			$followerLocation["followerid"] = $followerid;
		}
		return $followerLocation;
	}


	/**
	 * 拼接用户手机信息数组
	 */
	private function getFollowerMobileInfoArr($phoneData, $userData, $defaultAvatarUrl, $appId, $followerid, $openId, $uniqueId)
	{
		if (!empty($phoneData["model"])) {
			$mobileInfo["model"] = $phoneData["model"];
		}
		if (!empty($phoneData["pixelRatio"])) {
			$mobileInfo["pixelRatio"] = $phoneData["pixelRatio"];
		}
		if (!empty($phoneData["windowWidth"])) {
			$mobileInfo["windowWidth"] = $phoneData["windowWidth"];
		}
		if (!empty($phoneData["windowHeight"])) {
			$mobileInfo["windowHeight"] = $phoneData["windowHeight"];
		}
		if (!empty($phoneData["system"])) {
			$mobileInfo["system"] = $phoneData["system"];
		}
		if (!empty($phoneData["language"])) {
			$mobileInfo["language_sys"] = $phoneData["language"];
		}
		if (!empty($phoneData["screenWidth"])) {
			$mobileInfo["screenWidth"] = $phoneData["screenWidth"];
		}
		if (!empty($phoneData["screenHeight"])) {
			$mobileInfo["screenHeight"] = $phoneData["screenHeight"];
		}
		if (!empty($phoneData["brand"])) {
			$mobileInfo["brand"] = $phoneData["brand"];
		}
		if (!empty($phoneData["fontSizeSetting"])) {
			$mobileInfo["fontSizeSetting"] = $phoneData["fontSizeSetting"];
		}
		if (!empty($phoneData["platform"])) {
			$mobileInfo["platform"] = $phoneData["platform"];
		}
		if (!empty($phoneData["SDKVersion"])) {
			$mobileInfo["SDKVersion"] = $phoneData["SDKVersion"];
		}
		if (!empty($phoneData["projectname"])) {
			$mobileInfo["projectname"] = $phoneData["projectname"];
		}
		if (!empty($phoneData["wechatVersion"])) {
			$mobileInfo["wechatVersion"] = $phoneData["wechatVersion"];
		}
		if (!empty($openId)) {
			$mobileInfo["openid"] = $openId;
		}
		if (!empty($userData["avatarUrl"])) {
			$mobileInfo["avatarUrl"] = $userData["avatarUrl"];
		} else {
			$mobileInfo["avatarUrl"] = $defaultAvatarUrl;
		}
		if (!empty($userData["city"])) {
			$mobileInfo["city"] = $userData["city"];
		}
		if (!empty($userData["country"])) {
			$mobileInfo["country"] = $userData["country"];
		}
		if (!empty($userData["gender"])) {
			$mobileInfo["gender"] = $userData["gender"];
		}
		if (!empty($userData["language"])) {
			$mobileInfo["language_user"] = $userData["language"];
		}
		if (!empty($userData["nickName"])) {
			$mobileInfo["nickName"] = $userData["nickName"];
		}
		if (!empty($userData["province"])) {
			$mobileInfo["province"] = $userData["province"];
		}
		if (!empty($uniqueId)) {
			$mobileInfo["uniqueid"] = $uniqueId;
		}
		if (!empty($appId)) {
			$mobileInfo["appid"] = $appId;
		}
		if (!empty($followerid)) {
			$mobileInfo["followerid"] = $followerid;
		}
		return $mobileInfo;

	}


	/**
	 * 拼接用户手机网络信息数组
	 */
	private function getFollowerMobileNetworInfoArr($defaultAvatarUrl, $phoneData, $userData, $uniqueId, $appId, $followerid, $mombileid, $openId)
	{
		if (!empty($phoneData["model"])) {
			$networkInfo["model"] = $phoneData["model"];
		}
		if (!empty($phoneData["networkType"])) {
			$networkInfo["networkType"] = $phoneData["networkType"];
		}
		if (!empty($openId)) {
			$networkInfo["openid"] = $openId;
		}
		if (!empty($userData["avatarUrl"])) {
			$networkInfo["avatarUrl"] = $userData["avatarUrl"];
		} else {
			$networkInfo["avatarUrl"] = $defaultAvatarUrl;
		}
		if (!empty($userData["city"])) {
			$networkInfo["city"] = $userData["city"];
		}
		if (!empty($userData["country"])) {
			$networkInfo["country"] = $userData["country"];
		}
		if (!empty($userData["gender"])) {
			$networkInfo["gender"] = $userData["gender"];
		}
		if (!empty($userData["language"])) {
			$networkInfo["language_user"] = $userData["language"];
		}
		if (!empty($userData["nickName"])) {
			$networkInfo["nickName"] = $userData["nickName"];
		}
		if (!empty($userData["province"])) {
			$networkInfo["province"] = $userData["province"];
		}
		if (!empty($uniqueId)) {
			$networkInfo["uniqueid"] = $uniqueId;
		}
		if (!empty($appId)) {
			$networkInfo["appid"] = $appId;
		}
		if (!empty($followerid)) {
			$networkInfo["followerid"] = $followerid;
		}
		if (!empty($mombileid)) {
			$networkInfo["mobileid"] = $mombileid;
		}
		return $networkInfo;

	}


	/**
	 * 拼接小程序事件信息数组
	 */
	private function getEventInfoArr($data, $event_desc, $event_type, $component, $touches, $area, $duration, $phoneData, $userData, $defaultAvatarUrl, $openId, $uniqueId,
	                                 $appId, $followerid, $messageId, $object, $event_detail)
	{
		if (!empty($data["time"])) {
			$eventInfo["event_timestamp"] = $data["time"];
		}
		if (!empty($data["pageUrl"])) {
			$eventInfo["event_page"] = urldecode($data["pageUrl"]);
		}
		if (!empty($data["pageTitle"])) {
			$eventInfo["page_title"] = $data["pageTitle"];
		}
		if (!empty($event_desc)) {
			$eventInfo["event_desc"] = $event_desc;
		}
		if (!empty($event_type)) {
			$eventInfo["event_type"] = $event_type;
		}
		if (!empty($component)) {
			$eventInfo["component"] = json_encode($component);
		}
		if (!empty($touches)) {
			$eventInfo["touches"] = json_encode($touches);
		}
		if (!empty($area)) {
			$eventInfo["area"] = json_encode($area);
		}
		if (!empty($duration)) {
			$eventInfo["duration"] = $duration;
		}
		if (!empty($phoneData["model"])) {
			$eventInfo["model"] = $phoneData["model"];
		}
		if (!empty($phoneData["pixelRatio"])) {
			$eventInfo["pixelRatio"] = $phoneData["pixelRatio"];
		}
		if (!empty($phoneData["pixelRatio"])) {
			$eventInfo["pixelRatio"] = $phoneData["pixelRatio"];
		}
		if (!empty($phoneData["windowWidth"])) {
			$eventInfo["windowWidth"] = $phoneData["windowWidth"];
		}
		if (!empty($phoneData["windowHeight"])) {
			$eventInfo["windowHeight"] = $phoneData["windowHeight"];
		}
		if (!empty($phoneData["system"])) {
			$eventInfo["system"] = $phoneData["system"];
		}
		if (!empty($phoneData["language"])) {
			$eventInfo["language_sys"] = $phoneData["language"];
		}
		if (!empty($phoneData["screenWidth"])) {
			$eventInfo["screenWidth"] = $phoneData["screenWidth"];
		}
		if (!empty($phoneData["screenHeight"])) {
			$eventInfo["screenHeight"] = $phoneData["screenHeight"];
		}
		if (!empty($phoneData["brand"])) {
			$eventInfo["brand"] = $phoneData["brand"];
		}
		if (!empty($phoneData["fontSizeSetting"])) {
			$eventInfo["fontSizeSetting"] = $phoneData["fontSizeSetting"];
		}
		if (!empty($phoneData["platform"])) {
			$eventInfo["platform"] = $phoneData["platform"];
		}
		if (!empty($phoneData["SDKVersion"])) {
			$eventInfo["SDKVersion"] = $phoneData["SDKVersion"];
		}
		if (!empty($phoneData["projectname"])) {
			$eventInfo["projectname"] = $phoneData["projectname"];
		}
		if (!empty($phoneData["wechatVersion"])) {
			$eventInfo["wechatVersion"] = $phoneData["wechatVersion"];
		}
		if (!empty($phoneData["networkType"])) {
			$eventInfo["networkType"] = $phoneData["networkType"];
		}
		if (!empty($phoneData["IP"])) {
			$eventInfo["IP"] = $phoneData["IP"];
		}
		if (!empty($openId)) {
			$eventInfo["openid"] = $openId;
		}
		if (!empty($userData["avatarUrl"])) {
			$eventInfo["avatarUrl"] = $userData["avatarUrl"];
		} else {
			$eventInfo["avatarUrl"] = $defaultAvatarUrl;
		}
		if (!empty($userData["city"])) {
			$eventInfo["city"] = $userData["city"];
		}
		if (!empty($userData["country"])) {
			$eventInfo["country"] = $userData["country"];
		}
		if (!empty($userData["gender"])) {
			$eventInfo["gender"] = $userData["gender"];
		}
		if (!empty($userData["language"])) {
			$eventInfo["language_user"] = $userData["language"];
		}
		if (!empty($userData["nickName"])) {
			$eventInfo["nickName"] = $userData["nickName"];
		}
		if (!empty($userData["province"])) {
			$eventInfo["province"] = $userData["province"];
		}
		if (!empty($userData["latitude"])) {
			$eventInfo["latitude"] = $userData["latitude"];
		}
		if (!empty($userData["longitude"])) {
			$eventInfo["longitude"] = $userData["longitude"];
		}
		if (!empty($userData["unionid"])) {
			$eventInfo["unionId"] = $userData["unionid"];
		}
		if (!empty($data["scene"])) {
			$eventInfo["scene"] = $data["scene"];
		}
		if (!empty($uniqueId)) {
			$eventInfo["uniqueid"] = $uniqueId;
		}
		if (!empty($appId)) {
			$eventInfo["appid"] = $appId;
		}
		if (!empty($followerid)) {
			$eventInfo["followerid"] = $followerid;
		}
		if (!empty($messageId)) {
			$eventInfo["messageid"] = $messageId;
		}
		if (!empty($object["dataJson"])) {
			$eventInfo["jsonContent"] = $object["dataJson"];
		}
		if (!empty($event_detail)) {
			$eventInfo["detail"] = $event_detail;
		}
		if (!empty($data["customOption"])) {
			$eventInfo["customOption"] = json_encode($data["customOption"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
		return $eventInfo;
	}


}
