<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/2/27
 * Time: 10:03
 */
namespace app\miniprogram\util;


use app\miniprogram\common\Logic;

class NetworkUtil extends Logic
{

	/**get请求
	 * @param $url
	 * @return mixed
	 */
	static public function getCurl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //https请求 不验证证书 其实只用这个就可以了
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //https请求 不验证HOST
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	/**
	 * post请求
	 * @param $url
	 * @param $data
	 * @param string $cookie
	 * @param null $proxy
	 * @return bool|string
	 */
	static public function post($url, $data, $cookie = '', $proxy = null)
	{

		if (!$url) return false;
		$ssl = substr($url, 0, 8) == 'https://' ? true : false;
		$curl = curl_init();
		if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
		if (substr($url, 0, 8) == "https://") {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36 Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8");
		curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//Post提交的数据包

		// 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
		curl_setopt($curl, CURLOPT_HEADER, true);


		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($curl);
		// 获得响应结果里的：头大小
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		// 根据头大小去获取头信息内容
		$header = substr($content, 0, $headerSize);
		curl_close($curl);
		return substr($content,$headerSize);
	}

	/**
	 * upload请求
	 * @param $url
	 * @param $data
	 * @param string $cookie
	 * @param null $proxy
	 * @return bool|string
	 */
	static public function upload($url, $data, $cookie = '', $proxy = null)
	{

		if (!$url) return false;
		$ssl = substr($url, 0, 8) == 'https://' ? true : false;
		$curl = curl_init();
		if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
		if (substr($url, 0, 8) == "https://") {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36 Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8");
		curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//Post提交的数据包

		// 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
		curl_setopt($curl, CURLOPT_HEADER, true);


		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($curl);
		// 获得响应结果里的：头大小
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		// 根据头大小去获取头信息内容
		$json = substr($content, $headerSize);
		curl_close($curl);
		return $json;
	}

	/**
	 * 获取IP地址
	 */
	static public function getIP()
	{
		if (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_X_FORWARDED')) {
			$ip = getenv('HTTP_X_FORWARDED');
		} elseif (getenv('HTTP_FORWARDED_FOR')) {
			$ip = getenv('HTTP_FORWARDED_FOR');
		} elseif (getenv('HTTP_FORWARDED')) {
			$ip = getenv('HTTP_FORWARDED');
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

}