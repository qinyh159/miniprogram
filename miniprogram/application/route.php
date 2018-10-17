<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

Route::rule('test','mini/test/index');
Route::rule('getLogisticsInfo','mini/test/getLogisticsInfo');
Route::rule('downLoadExcel','mini/test/downLoadExcel');
Route::rule('saveDataToWocf','mini/test/saveDataToWocf');
Route::rule('verifyMobile','mini/test/verifyMobile');
Route::rule('test1','mini/test/test1');
Route::rule('test2','mini/test/test2');
Route::rule('getMiniProgramsEvent','miniprogram/Index/getMiniProgramsEvent');