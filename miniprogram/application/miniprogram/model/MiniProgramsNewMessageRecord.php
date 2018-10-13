<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/2/9
 * Time: 15:44
 */
namespace app\miniapp\model;


use app\common\Model;

class MiniProgramsNewMessageRecord extends Model
{
    var $table = "mini_programs_newmessage_record";

    public function findNewMessageRecordByFollowerIdAndAppId($followerid,$appid)
    {
        $followerid = (int)$followerid;
        $appid = (int)$appid;
        $messageNewRecordMode = new \app\miniapp\model\MiniProgramsNewMessageRecord();
        $sql = "select * from mini_programs_newmessage_record where followerid = :followerid and appid = :appid ";

        $newMessageInfo = $messageNewRecordMode->query($sql, ['followerid' => $followerid, "appid" => $appid]);

        if(empty($newMessageInfo)){
            return [];
        }
        return $newMessageInfo[0];
    }


    public function insertNewMessageRecord($message)
    {
        $messageNewRecordMode = new \app\miniapp\model\MiniProgramsNewMessageRecord();
        return $messageNewRecordMode->name("mini_programs_newmessage_record")->insertGetId($message);
    }

    public function updateNewMessageRecord($id,$messageId)
    {
        $messageNewRecordMode = new \app\miniapp\model\MiniProgramsNewMessageRecord();
        $messageNewRecordMode->db()->where("id", $id)->update(
        [
            "messageid" => $messageId,
            "time" => time()
        ]
    );
    }
}
