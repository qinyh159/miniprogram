<?php


namespace app\miniapp\model;


use app\common\Model;

class User extends Model
{
    var $table = "mini_programs_user";
    protected $autoWriteTimestamp = true;
    protected static $whereDefault = ['status'=> 0];

    public function roles()
    {
        return $this->belongsToMany('MiniProgramsRole','mini_programs_role_user','role_id','user_id')->where(self::$whereDefault);
    }

    public static function getSimpleUserList()
    {
        return self::where(self::$whereDefault)->field('id,name,email_address,create_time,update_time')->select();
    }

    public static function getUserInfo($id)
    {
        return self::with(['roles'])->find($id)->hidden(['password']);
    }

    public static function getSelfInfo($id)
    {
        return self::find($id)->hidden(['password']);
    }

    /**
     * 插入更新数据进行验证
     * @param $data
     * @param $type
     */
    public static function validaSave($data,$id,$type)
    {
        $model = self::where('id','<>',$id)->where('name=:name or email_address=:email_address')
            ->bind(['name'=>$data['name'],'email_address'=>$data['email_address']])->find();
        switch ($type){
            case 'add' :
                if ($model){
                    if (empty($data['password']))
                        $msg = "请填写密码";
                    elseif ($model['name'] == $data['name'])
                        $msg = "此名字已经被使用了";
                    elseif ($model['email_address'] == $data['email_address'])
                        $msg = "此邮箱已经被使用了";
                }else{
                    $model = self::create($data);
                    $id = $model['id'];
                }
                break;
            case 'edit' :
                if ($model && $model['id'] != $id){
                    if ($model['name'] == $data['name'])
                        $msg = "此名字已经被使用了";
                    elseif ($model['email_address'] == $data['email_address'])
                        $msg = "此邮箱已经被使用了";
                }else{
                    $model = self::update($data, ['id' => $id]);
                }
                break;
        }

        if (isset($msg)){
            return ['errorCode'=>-1,'msg'=>$msg];
        }elseif(!$model){
            return ['errorCode'=>-1,'msg'=>'数据错误'];
        }else{
            return $id;
        }
    }
}
