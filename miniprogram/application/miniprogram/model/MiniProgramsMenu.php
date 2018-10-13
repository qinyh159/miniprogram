<?php

namespace app\miniapp\model;

use app\common\Model as BaseModel;

class MiniProgramsMenu extends BaseModel
{
    protected $autoWriteTimestamp = true;

    public function roles()
    {
        return $this->belongsToMany('MiniProgramsRole','mini_programs_role_menu','role_id','menu_id');
    }

    public static function getListJson()
    {
        return self::with(['roles'])->order('sort')->select();
    }

    public static function getParentsMenu()
    {
        return self::where(['parent_id'=>0])->select();
    }

    public static function delMenuAndChild($id)
    {
        self::where(['id'=>$id])->delete();
        self::where(['parent_id'=>$id])->delete();
    }

    public function getUserTreeMenu($uid,$isAdmin)
    {

    }
}