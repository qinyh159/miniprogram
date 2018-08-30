<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/7/20
 * Time: 11:54
 */
namespace app\mini\controller;

use think\Controller;

class Index extends Controller
{
	public function index()
	{
		return phpinfo();


		/*$timestamp = $this->msectime();
		$sysId = "chudian";
		$key = "c00723d4dd2343ea9cfebf7f6362a51d";
		echo $timestamp."\n";
		echo $this->createSign($sysId,$timestamp,$key);*/


		/*$baseOpenidUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbd883360d38aff65&response_type=code&scope=snsapi_base&state=397" .
				"&redirect_uri=";
		$midleUrl = "http://ny.gx10010.com/event/redirect/getOpenId?sysId=chudian&backurl=";
		$redirectUri = "http://wocf.gx10010.com/app/index.php?i=397&c=entry&rid=6010&id=159990&do=index&m=tyzm_tuanyuan";
		$redirectUri = urlencode($redirectUri);
		$url = $baseOpenidUrl.urlencode($midleUrl.$redirectUri);
		$openidUrl = $this->getCurUrl($url);
		echo $openidUrl;exit;
		if (empty($openid)) {
			echo "get openid error";
		}
		$_SESSION['oauth_openid'] = $openid;*/

		/*$sysId = "chudian";
		$timestamp = $this->msectime();
		$openid="oeU5qv5L3vW5EWmlB2OZudtc5yIA";
		$key = "c00723d4dd2343ea9cfebf7f6362a51d";
		print_r($this->getUserInfo($sysId,$timestamp,$key,$openid));
		return $this->fetch('index');*/
	}


	function getUserInfo($sysId,$timestamp,$key,$openid)
	{

		$str = "keyword=".$openid."&sysId=".$sysId."&timestamp=".$timestamp."&type=1"."&key=".$key;
		$newdata = strtoupper(md5($str));
		$url = "http://ny.gx10010.com/event/aipservice/getUserInfoByOpenId";
		$data = json_encode(["keyword"=>$openid,"sysId"=>"chudian","timestamp"=>$timestamp,"type"=>1,"sign"=>$newdata]);
		print_r($newdata.'\n');
		print_r($data.'\n');
		//$response = ihttp_request($url, $data);
		//return $response;
	}



	private function createSign($sysId,$timestamp,$key)
	{

		$str = "sysId=".$sysId."&timestamp=".$timestamp."&key=".$key;
		//print_r($str."\n") ;
		return strtoupper(md5($str));
	}

	private function msectime() {
		list($msec, $sec) = explode(' ', microtime());
		return $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	}

	/**
	 * 获取重定向后的地址（备用）
	 * @param $url
	 * @return mixed
	 */
	function getCurUrl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		// 不需要页面内容
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		// 不直接输出
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 返回最后的Location
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_exec($ch);
		$info = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);
		return $info;
	}
}


