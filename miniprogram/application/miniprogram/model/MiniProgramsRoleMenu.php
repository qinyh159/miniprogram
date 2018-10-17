<?php

namespace app\miniprogram\Model;

use app\miniprogram\common\Model as BaseModel;

class MiniProgramsRoleMenu extends BaseModel
{
    protected $autoWriteTimestamp = true;

    public function roles()
    {
        return $this->belongsToMany('MiniProgramsRole','mini_programs_role_user','role_id','user_id');
    }
}