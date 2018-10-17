<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/10/13
 * Time: 11:41
 */
namespace app\miniprogram\controller;
use app\miniprogram\model\MiniProgramsApplist;

use think\cache\driver\Redis;
use think\Config;
use app\miniprogram\common\Controller;
use app\miniprogram\model\GetOpenId;
use app\miniprogram\common\WXBizDataCrypt;
use app\miniprogram\util\NetworkUtil;
use app\miniprogram\modelMiniProgramsApplist;
use app\miniprogram\model\MiniProgramsFollower;
use app\miniprogram\model\MiniProgramsImg;
class Index extends Controller
{

	public function __construct()
	{
		self::$allowed = array('getminiprogramsevent','getopenid','checkopenid','getminiprogramsid','getcuruserkey');
		parent::__construct();
	}

	/**
	 * 获取小程序事件,并插入数据库（方法有更新）
	 * 请按需要修改Model路径
	 */
	function getMiniProgramsEvent()
	{
		$json = isset($_POST["HTTP_RAW_POST_DATA"]) ? $_POST["HTTP_RAW_POST_DATA"] : file_get_contents("php://input");
		$ip = NetworkUtil::getIP();
		$json = trim((String)$json);

		if( empty($json) ) {
			// echo json_encode(["errmsg"=>"empty data"]);
			return;
		}

		$object = json_decode($json, true);
		if (empty($object)) {
			// echo json_encode(["errmsg"=>"Invalid json"]);
			return;
		}

		//判断数据有效性
		$uniqueId = isset($object["uniqueId"]) ? $object["uniqueId"] : "";
		if (empty($uniqueId)) {
			// echo json_encode(["errmsg"=>"uniqueId"]);
			return;
		}

		$applist = "SELECT * FROM `mini_programs_applist` WHERE uniqueid= :uniqueId";
		$applistModel = new MiniProgramsApplist();
		$applistInfo = $applistModel->query($applist, ['uniqueId' => $uniqueId]);

		if (empty($applistInfo) || count($applistInfo) < 0) {
			// echo json_encode(["errmsg"=>"app does not exist"]);
			return;
		}

		$redis =new \Redis();
		Config::load('config.php');
		$redisconfig = Config::get('REDIS');
		$result = $redis->connect($redisconfig["REDIS_HOST"], $redisconfig["REDIS_PORT"]);

		$pathInfo = '';
		if (!empty($_SERVER['PATH_INFO'])) {
			$pathInfo = $_SERVER['PATH_INFO'];
		} else if (!empty($_SERVER['REQUEST_URI'])) {
			$pathInfo = $_SERVER['REQUEST_URI'];
		} else if (!empty($_SERVER['QUERY_STRING'])) {
			$pathInfo = $_SERVER['QUERY_STRING'];
		} else if (!empty($_SERVER['argv'][1])) {
			$pathInfo = $_SERVER['argv'][1];
		}


		//debug表中保存后端得到的IP地址
		/*if (!empty($json)) {
			$debugModel = new \app\miniprogram\model\MiniProgramsDebug();
			$data = [
				"time" => time(),
				"ip_address" => isset($ip) ? $ip : "",
				"path" => isset($pathInfo) ? $pathInfo : "",
				"request" => $json,
			];

			$debugModel->name("mini_programs_debug")->insert($data);
		}*/


		//将原始数据放入debug队列中
		$keydebug = "mini_programs_debug_json";

		$debugJson = [
			"debugJson" => $json,
			"IP" => $ip,
			'path' => $pathInfo
		];

		$debugjson = json_encode($debugJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$redis->rpush($keydebug, $debugjson);




		//测试内存溢出（debug）
		/* $keydebugtest = "mini_programs_debug_json_test";

		 $redis->rpush($keydebugtest, $json);*/


		//将原始数据放入正常数据的队列中
		$keydata = "mini_programs_data_message";

		$data = [
			"dataJson" => $json,
			"IP" => $ip,
			"type" => "json"
		];

		$dataMessage = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$redis->rpush($keydata, $dataMessage);

		echo json_encode(["result"=>"sucess"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	public function getCurUserKey() {
		ob_end_clean();
		header('Content-Type: application/json');
		$json = isset($_POST["HTTP_RAW_POST_DATA"]) ? $_POST["HTTP_RAW_POST_DATA"] : file_get_contents("php://input");
		$json = trim($json);
		$json = json_decode($json, true);

		$arrResutl = [
			'errcode' => 0,
			'errmsg' => '',
			'curUserKey' => '',
		];

		if( !$json ) {
			$arrResutl['errcode'] = 1;
			$arrResutl['errmsg'] = json_last_error_msg();
			echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
			return;
		}

		$uniqueId = isset($json['uniqueId']) ? trim($json['uniqueId']) : '';
		$curUserKey = isset($json['curUserKey']) ? trim($json['curUserKey']) : '';
		$openid = isset($json['openid']) ? trim($json['openid']) : '';

		if( empty($openid) || empty($uniqueId) || empty($curUserKey) ) {
			return;
		}

		$applist = "SELECT * FROM `mini_programs_applist` WHERE uniqueid= :uniqueId";
		$applistModel = new MiniProgramsApplist();
		$applistInfo = $applistModel->query($applist, ['uniqueId' => $uniqueId]);

		if ( empty($applistInfo) ) {
			$arrResutl['errcode'] = 2;
			// $arrResutl['errmsg'] = 'APP is empty';
			echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
			return;
		}

		$appid = $applistInfo[0]['id'];

		$followerModel = new \app\miniprogram\model\MiniProgramsFollower();

		$sql = "select id,curUserKey from mini_programs_follower where openid = :openid and appid = :appid order by id asc limit 1";
		$follower = $followerModel->query($sql, ['openid' => $openid, 'appid' => $appid]);
		if ( empty($follower) ) {
			$arrResutl['errcode'] = 3;
			// $arrResutl['errmsg'] = '用户不存在';
			echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
			return;
		}

		$arrResutl['curUserKey'] = $follower[0]['curUserKey'];

		if( $follower[0]['curUserKey'] != $curUserKey ) {
			$queue = 'merge_follower';

			$redisNFSconfig = Config::get('REDIS_NFS');
			if( empty($redisNFSconfig) ) {
				$redisNFSconfig = Config::get('REDIS');;
			}
			$redis_nfs = new \Redis();
			$result_nfs = $redis_nfs->connect($redisNFSconfig["REDIS_HOST"], $redisNFSconfig["REDIS_PORT"]);

			if ( !$result_nfs ) {
				$arrResutl['errcode'] = 4;
				// $arrResutl['errmsg'] = 'redis 异常';
				echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
				return;
			}

			$data = [
				'app_id' => $appid,
				'openid' => $openid,
				'curUserKey' => $curUserKey
			];

			$data = json_encode($data, JSON_UNESCAPED_UNICODE);
			$redis_nfs->rpush($queue, $data);
		}
		echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
	}

	public function getOpenId() {
		ob_end_clean();
		header('Content-Type: application/json');
		$json = isset($_POST["HTTP_RAW_POST_DATA"]) ? $_POST["HTTP_RAW_POST_DATA"] : file_get_contents("php://input");
		$json = trim($json);
		$json = json_decode($json, true);

		$arrResutl = [
			'result' => 'sucess',
			'time' => 5000,
		];

		if( !$json ) {
			$arrResutl = [
				'result' => json_last_error_msg(),
				'time' => 5000,
			];
			echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
			return;
		}

		$data = [
			'js_code' => isset($json['js_code']) ? trim($json['js_code']) : '',
			'grant_type' => isset($json['grant_type']) ? trim($json['grant_type']) : '',
			'uniqueId' => isset($json['uniqueId']) ? trim($json['uniqueId']) : '',
			'appId' => isset($json['appId']) ? trim($json['appId']) : '',
			'appsecret' => isset($json['appsecret']) ? trim($json['appsecret']) : '',
			'curUserKey' => isset($json['curUserKey']) ? trim($json['curUserKey']) : '',
			'iv' => isset($json['iv']) ? trim($json['iv']) : '',
			'encryptedData' => isset($json['encryptedData']) ? trim($json['encryptedData']) : '',

		];

		$queue = 'getOpenId';

		Config::load('config.php');
		$redisconfig = Config::get('REDIS');
		$redis = new \Redis();
		$result = $redis->connect($redisconfig["REDIS_HOST"], $redisconfig["REDIS_PORT"]);

		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		$redis->rpush($queue, $data);
		echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
		return;
	}

	public function checkOpenId() {
		ob_end_clean();
		header('Content-Type: application/json');
		$json = isset($_POST["HTTP_RAW_POST_DATA"]) ? $_POST["HTTP_RAW_POST_DATA"] : file_get_contents("php://input");
		$json = trim($json);
		$json = json_decode($json, true);

		$arrResutl = [
			'errcode' => 0,
			'errmsg' => '',
			'time' => 5000,
		];

		if( !$json ) {
			$arrResutl['errcode'] = 1;
			$arrResutl['errmsg'] = json_last_error_msg();
			echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
			return;
		}

		$uniqueId = isset($json['uniqueId']) ? trim($json['uniqueId']) : '';
		$curUserKey = isset($json['curUserKey']) ? trim($json['curUserKey']) : '';

		$appListModel = new MiniProgramsApplist();
		$appListInfo = $appListModel->getAppByUniqueId($uniqueId);
		if(empty($appListInfo)){
			$arrResutl['errcode'] = 2;
			$arrResutl['errmsg'] = '小程序不存在';
			echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
			return;
		}

		$followerModel = new MiniProgramsFollower();

		$sql = "select id,unionId,openid,sessionKey from mini_programs_follower where curUserKey = :curUserKey and uniqueid = :uniqueId order by id asc limit 1";
		$follower = $followerModel->query($sql, ['curUserKey' => $curUserKey, 'uniqueId' => $uniqueId]);
		if (empty($follower) && count($follower) <= 0) {
			$arrResutl['errcode'] = 2;
			$arrResutl['errmsg'] = '用户不存在';
			echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
			return;
		}

		//返回了sessionkey或者数据库已经存在sessionkey,当小程序端无法通过wx.login的方式获取到unionId,则需要解密加密数据
		if(empty($follower[0]['unionId'])){
			$getOpenidModel = new GetOpenId();
			$getOpenidInfo = $getOpenidModel->getItemByCurUserKey($curUserKey);

			if(empty($getOpenidInfo)){
				$arrResutl['errcode'] = 2;
				$arrResutl['errmsg'] = '用户不存在';
				echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
				return;
			}
			$object = json_decode($getOpenidInfo["json"], true);
			$iv = $object["iv"];
			$encryptedData = $object["encryptedData"];
			$pc = new WXBizDataCrypt($appListInfo["appid"], $follower[0]["sessionKey"]);
			$errCode = $pc->decryptData($encryptedData, $iv, $encryptedInfo);

			if ($errCode == 0) {
				$userobject = json_decode($encryptedInfo, true);
				$follower[0]['unionId'] = $userobject["unionId"];
				$followerModel->db()->where("id", $follower[0]["id"])->update(["unionId" => $userobject["unionId"]]);
			}

		}

		$arrResutl['unionId'] = $follower[0]['unionId'];
		$arrResutl['openId'] = $follower[0]['openid'];
		$arrResutl['sessionKey'] = $follower[0]['sessionKey'];

		echo json_encode($arrResutl,JSON_UNESCAPED_UNICODE);
		return;
	}

	/**
	 * 请求获取openid
	 */
	function getMiniProgramsId()
	{
		$json = isset($_POST["HTTP_RAW_POST_DATA"]) ? $_POST["HTTP_RAW_POST_DATA"] : file_get_contents("php://input");
		$json = trim((string)$json);
		$object = json_decode($json, true);


		Config::load('config.php');
		$redisconfig = Config::get('REDIS');
		$redis = new \Redis();
		$result = $redis->connect($redisconfig["REDIS_HOST"], $redisconfig["REDIS_PORT"]);
		//判断数据有效性
		$uniqueId = isset($object["uniqueId"]) ? $object["uniqueId"] : "";

		if ($result) {
			$uni = $redis->get($uniqueId);

			if ($uni != true) {
				//当前数据不在redis中需要查找数据库
				$appModel = new MiniProgramsApplist();
				$appInfo = $appModel->getAppByUniqueId($uniqueId);
				if(empty($appInfo)){
					return "当前不存在该小程序";
				}
				$redis->set($uniqueId, $uniqueId);

			}
		} else {
			echo "redis 连接失败";
			return;
		}
		if (!empty($object["uniqueId"]) && !empty($object["grant_type"]) && !empty($object["uniqueId"]) && !empty($object["appId"]) && !empty($object["appsecret"])) {
			// 发送 res.code 到后台换取 openId, sessionKey, unionId
			//$APP_ID = 'wx19a780d3d71f6f60';//输入小程序appid（根据实际情况修改）
			//$APP_SECRET = 'ced762dca9aafa6420fe4924fa3dabae';//输入小程序app_secret（根据实际情况修改）
			$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $object["appId"] . '&secret=' . $object["appsecret"] . '&js_code=' . $object['js_code'] . '&grant_type=' . $object['grant_type'];
			$json = $this->getCurl($url);
			echo $json;
		}


	}


	/**get请求
	 * @param $url
	 * @return mixed
	 */
	private function getCurl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}


	/**
	 * 将图片数据插入数据库中（新增方法）
	 * 请按需要修改Model路径
	 */
	function insertImgMsg()
	{
		$json = isset($_POST["HTTP_RAW_POST_DATA"]) ? $_POST["HTTP_RAW_POST_DATA"] : file_get_contents("php://input");
		$object = json_decode($json, true);

		$imgpath = $this->getImageContentBy($object);

		//查看当前是否已经存在数据，有则更新图片路径
		$miniImgModel = new MiniProgramsImg();
		$sql = "select * from mini_programs_img where pagepath = '" . $object["pagepath"] . "' and projectname = '" . $object["projectname"] . "'";

		$imgInfo = $miniImgModel->db()->query($sql);
		if ($imgInfo != null && !empty($imgInfo)) {
			$miniImgModel->db()->where("id", $imgInfo[0]["id"])->update(["projectname" => $object["projectname"], "imgpath" => $imgpath, "filename" => $object["filename"]]);
		} else {
			$eventInfo = [
				"projectname" => $object["projectname"],
				"pagepath" => $object["pagepath"],
				"imgpath" => $imgpath,
				"filename" => $object["filename"]
			];
			$miniImgModel->db()->insert($eventInfo);
		}

	}

	/**
	 * 获取图片实际信息（新增方法）
	 * 请按实际修改路径
	 * @param $obj
	 * @return string
	 */
	public
	function getImageContentBy($obj)
	{
		$uri = $this->getFilePath($obj["filename"]);
		$imgInfo = getimagesize("./resource/" . $obj["filename"]);
		if ($imgInfo[1] > 1000 || $imgInfo[0] > 1000) {
			$height = $imgInfo[1] * 0.1;
			$width = $imgInfo[0] * 0.1;
		} else if ($imgInfo[1] > 500 || $imgInfo[0] > 500) {
			$height = $imgInfo[1] * 0.2;
			$width = $imgInfo[0] * 0.2;
		} else if ($imgInfo[1] > 230 || $imgInfo[0] > 230) {
			$height = $imgInfo[1] * 0.8;
			$width = $imgInfo[0] * 0.8;
		} else {
			$height = $imgInfo[1] * 0.8;
			$width = $imgInfo[0] * 0.8;
		}

		$result = "<img src='$uri' style='height: " . $height . "px; width: " . $width . "px;'/>";

		return $result;
	}

	/**
	 * 获取相对路径（新增方法）
	 * 请按实际修改路径
	 * @param $fileName
	 * @return string
	 */
	public
	function getFilePath($fileName)
	{
		return "/miniprogram/resource/file/name/" . $fileName;
	}


	/**
	 * 上传图片（新增方法）
	 */
	public
	function upload()
	{
		$this->check_token();
		$fileName = $this->uploadFile();
		if (self::is_not_json($fileName) != true) {
			echo $fileName;
			return;
		}
		$data = [
			'fileName' => $fileName
		];
		echo json_encode($data);
	}

	/**
	 * 查看是否为Json格式（新增方法）
	 * @param $str
	 * @return bool
	 */
	function is_not_json($str)
	{
		return is_null(json_decode($str));
	}

	/**
	 * 检查tooken（新增方法，按实际需要添加）
	 */
	private
	function check_token()
	{
		$signature = isset($_GET["signature"]) ? $_GET["signature"] : "";
		$timeStamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : "";
		$nonce = isset($_GET["nonce"]) ? $_GET["nonce"] : "";
		$token = "wwDd12da36J";
		$ticket = md5($token . $timeStamp . $nonce);
		if ($ticket != $signature) {
			echo 'error';
			die;
		}
	}

//请根据实际路径修改
	static $filePath = "./resource/";

	/**
	 * 上传文件（新增方法）
	 * @return string
	 */
	static public function uploadFile()
	{
		$file = request()->file('file');
		$info = $file->rule('uniqid')->move(self::$filePath);

		if ($info) {
			//是否为pc端发送图片
			if (!empty($_POST["lable"]) && isset($_POST["lable"])) {
				$suffixFilename = $info->getFilename();
				$newFileName = strval(rand(1, 9999)) . microtime(true);
				//copy(self::$filePath . $suffixFilename,self::$filePath . $newFileName);
				rename(self::$filePath . $suffixFilename, self::$filePath . $newFileName);
				$data = [
					'uploadStatus' => 1,
					'newFilename' => $newFileName,
					'type' => $_POST["lable"]
				];

				return json_encode($data);
			}
		} else {
			// 上传失败获取错误信息
			echo $file->getError();
			die();
		}
	}

	/**
	 *
	 */
	public function getRoleLeftNav()
	{
		//session_start();
		$sid   = empty($_SESSION['sid']) ? 0 : $_SESSION['sid'];
		$email = $_SESSION['email_address'];

		// 超级管理员可以访问所有
		if ($sid == 1 && $email == 'admin@panmeta.com'){

		} else {

		}
	}
}