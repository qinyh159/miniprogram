<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/2/6
 * Time: 17:49
 */
namespace app\miniapp\model;


use app\common\Model;

class MiniProgramsMessage extends Model
{
    var $table = "mini_programs_message";

    public function reply()
    {
        return $this->hasMany('MiniProgramsMessage','reply_id','id');
    }
    public function follow()
    {
        return $this->belongsTo('MiniProgramsFollower','followerid','id');
    }

    public function user()
    {
        return $this->belongsTo('User','respondent_id','id')->field('id,name,avatar');
    }

    public function insert($message)
    {
        $Mode = new \app\miniapp\model\MiniProgramsMessage();
        return $Mode->name("mini_programs_message")->insertGetId($message);
    }

    /**
     * 获取用户聊天记录
     */
    public static function getUserMsgByFollowerId($followerid,$messageid,$type)
    {
        $where = ['followerid' => $followerid, 'chattype' => array('in', '0,3')];

        if ($type)
            $where['id'] = $messageid;

        $data = self::where($where)->with(['reply', 'reply.user', 'follow'])->order('id desc')->paginate(10, true);

        return $data;
    }
}
