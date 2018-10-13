<?php
/**
 * Created by PhpStorm.
 * User: Alex.Ou
 * Date: 2016/6/12
 * Time: 10:22
 */

\think\Lang::range('en-us');

date_default_timezone_set('PRC');

if ( ! function_exists('base_url')) {
	function base_url($uri = '', $postfix = false) {
		return $uri . ($postfix ? (((strpos($uri, '?') > 0) ? '&' : '?'). 'v' . VERSION) : '');
	}
}

if ( ! function_exists('L')) {
	function L($key) {
		global $CFG;
		$language = app\common\localesModel::$language;
		return isset($CFG['locales'][$language][$key]) ? $CFG['locales'][$language][$key] : $key;
	}
}


function static_path($url=''){
	//return Request::instance()->root()."/static/".$url;
	return "/static/".$url;
}


function random_string($length=6, $type='string', $convert=0){
	$config = array(
		'number'=>'1234567890',
		'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
		'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
	);

	if(!isset($config[$type])) $type = 'string';
	$string = $config[$type];

	$code = '';
	$strlen = strlen($string) -1;
	for($i = 0; $i < $length; $i++){
		$code .= $string{mt_rand(0, $strlen)};
	}
	if(!empty($convert)){
		$code = ($convert > 0)? strtoupper($code) : strtolower($code);
	}
	return $code;
}

function redirect2($url, $params = [], $code = 302, $https = false){

	if($https){
		$_SERVER['HTTPS'] = "on";
		$_SERVER['SERVER_PORT'] = "443";
	}
	$url = url($url,'',true,true);    
	
	$response = new \think\response\Redirect($url);
	if (is_integer($params)) {
		$code   = $params;
		$params = [];
	}
	$response->code($code)->params($params);
	throw new \think\exception\HttpResponseException($response);
}

/**
 * 验证码检查，验证完后销毁验证码增加安全性 ,<br>返回true验证码正确，false验证码错误
 * @return boolean <br>true：验证码正确，false：验证码错误
 */
function check_verify_code(){
	$verify = new app\common\Verify();
	return $verify->check($_REQUEST['verify'], "");
}
/*
function url($url = '', $vars = '', $suffix = true, $domain = false, $https = false){

	if(!$https){
		unset($_SERVER["HTTPS"]);
		$_SERVER["SERVER_PORT"] = 80;
	}

	return Url::build($url, $vars, $suffix, $domain);
}*/