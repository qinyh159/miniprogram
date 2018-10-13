<?php

namespace app\miniapp\model;


use app\common\Model;

class Weixin extends Model {

	public function getAccessToken($app_id) {
		//查找当前accesstoken表中，是否存在该app的数据
		$acessToken = \app\miniapp\model\MiniProgramsAccessToken::getToken($app_id);
		//为空则请求微信服务器并保存返回的信息

		if( isset( $acessToken[0]["time"] ) && (time() - $acessToken[0]["time"]) <=7000 ) {
			return $acessToken[0]['accesstoken'];
		}

		$applistModel = new \app\miniapp\model\MiniProgramsApplist();
		$applist = $applistModel->getAppById($app_id);

		if (empty($applist) || count($applist) <= 0) {
			return;
		}

		$appId = $applist["appid"];
		$appSecret = $applist["appsecret"];
		//access_token的有效期目前为2个小时，需定时刷新获取access_token
		$getAccessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential" . "&appid=" . $appId . "&secret=" . $appSecret;

		$result = \app\miniapp\util\NetworkUtil::getCurl($getAccessTokenUrl);
		$tokenObject = json_decode($result, true);
		if( !isset( $tokenObject['access_token'] ) ) {
			return;
		}
		\app\miniapp\model\MiniProgramsAccessToken::insertToken($app_id, $tokenObject["access_token"]);
		return $tokenObject["access_token"];
	}

	public function getMiniProgramQrcode($access_token,$data) {
		$access_token = trim($access_token);
		$api = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$access_token}";
		$json = json_encode([
			'scene' => isset($data['scene']) ? trim($data['scene']) : '',
			'page' => isset($data['page']) ? trim($data['page']) : '',
			// 'page' => 'pages/main/main',
			'width' => isset($data['width']) ? intval($data['width']) : '',
			// 'line_color' => !empty($data['line_color']) ? trim($data['line_color']) : '{"r":0,"g":0,"b":0}',
			// 'auto_color' => isset($data['auto_color']) ? boolval($data['auto_color']) : false,
			'auto_color' => false,

		],JSON_UNESCAPED_UNICODE);

		$arrResult = [
			'errmsg' => '',
			'img' => '',
		];
		$result = \app\miniapp\util\NetworkUtil::post($api, $json);
		if( $result[0] == '{') {
			$arrResult['errmsg'] = $result;
			return $arrResult;
		}

		$dir =  'resource/miniapp_qrcode/'. date('Y') . '/' . date('m') . '/';
		if( !is_dir($dir) ) {
			mkdir($dir,0775,true);
		}

		$img = $dir . time() . '.png';
		file_put_contents($img, $result);
		$arrResult['img'] = $img;

		return $arrResult;
	}

	public function getQrcode($access_token,$data) {
		$access_token = trim($access_token);
		$api = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token={$access_token}";
		$json = json_encode([
			'path' => isset($data['page']) ? trim($data['page']) : '',
			// 'page' => 'pages/main/main',
			'width' => isset($data['width']) ? intval($data['width']) : '',

		],JSON_UNESCAPED_UNICODE);

		$arrResult = [
			'errmsg' => '',
			'img' => '',
		];
		$result = \app\miniapp\util\NetworkUtil::post($api, $json);
		if( $result[0] == '{') {
			$arrResult['errmsg'] = $result;
			return $arrResult;
		}
		
		$dir =  'resource/qrcode/'. date('Y') . '/' . date('m') . '/';
		if( !is_dir($dir) ) {
			mkdir($dir,0775,true);
		}
		
		$img = $dir . time() . '.png';
		file_put_contents($img, $result);

		$arrResult['img'] = $img;

		return $arrResult;
	}

}