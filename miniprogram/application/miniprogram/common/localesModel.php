<?php

/*
作者:moyancheng
创建时间:2016-05-30
最后更新时间:2016-05-30
*/
namespace app\miniprogram\common;
class localesModel
{
	public static $language = 'en_US';

	public function __construct() {
		global $CFG;
		if (empty($CFG['locales']['zh_CN'])) $CFG['locales']['zh_CN'] = include APP_PATH . 'miniprogram/locales/zh_CN.php';
		if (empty($CFG['locales']['en_US'])) $CFG['locales']['en_US'] = include APP_PATH . 'miniprogram/locales/en_US.php';
	}

	public function init() {
		$lang = isset( $_SERVER['ACCEPT-LANGUAGE'] ) ? $_SERVER['ACCEPT-LANGUAGE'] : '';
		if (substr($lang, 0, 5) == 'zh-CN') {
			self::$language = 'zh_CN';
		} else {
			self::$language = 'en_US';
		}
		if (isset($_COOKIE['locale_language'])) {
			if ($_COOKIE['locale_language'] == 'zh_CN') {
				self::$language = 'zh_CN';
			} else {
				self::$language = 'en_US';
			}
		}
		if (isset($_SESSION['current_locale'])) {
			if ($_SESSION['current_locale'] == 'zh_CN') {
				self::$language = 'zh_CN';
			} else {
				self::$language = 'en_US';
			}
		}
		if (isset($_GET['locale'])) {
			if ($_GET['locale'] == 'zh_CN') {
				self::$language = 'zh_CN';
			} else {
				self::$language = 'en_US';
			}
		}

	}

}