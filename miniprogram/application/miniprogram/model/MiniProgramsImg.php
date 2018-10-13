<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2017/11/22
 * Time: 17:42
 */
namespace app\miniapp\model;


use app\common\Model;

class MiniProgramsImg extends Model
{
    var $table = "mini_programs_img";

    public static function getOneById($id)
    {
        return self::find($id);
    }

    public static function getPageByAppid($appid)
    {
        return self::where(['appid'=>$appid])->select();
    }
}
