<?php
/**
 * Created by PhpStorm.
 * User: Mhj_pc
 * Date: 2018/4/17
 * Time: 11:45
 */

namespace app\miniprogram\Model;

use app\miniprogram\common\Model as BaseModel;
use think\__require_file;

class MiniProgramsRole extends BaseModel
{
    protected $autoWriteTimestamp = true;

    /**
     * 获取角色列表 , 除开已删除角色
     */
    public static function getList()
    {
        return self::where('status',0)->select();
    }

    public static function getRoleById($id)
    {
        $access = include_once APP_PATH.'permissions.php';

        if ($id > 0) {
            $role = self::find($id);
            $permissions = explode(',',$role['permissions']);

            foreach ($access as &$a) {
                if (isset($a['children'])){
                    foreach ($a['children'] as &$val) {
                        if (isset($val['children'])){
                            foreach ($val['children'] as &$v) {
                                $arr = explode(',',$v['controller_action']);
                                $result = array_intersect($permissions,$arr);
                                if (count($result) > 0){
                                    $v['checked'] = true;
                                }
                            }
                        }else{
                            $arr = explode(',',$val['controller_action']);
                            $result = array_intersect($permissions,$arr);
                            if (count($result) > 0){
                                $val['checked'] = true;
                            }
                        }
                    }
                } else {
                    $arr = explode(',',$a['controller_action']);
                    $result = array_intersect($permissions,$arr);
                    if (count($result) > 0){
                        $a['checked'] = true;
                    }
                }
            }
            $role['permissionsJson'] = $access;

            return $role;
        }else {
            return array('permissionsJson'=>$access);
        }
    }
}