<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2017/11/22
 * Time: 17:42
 */
namespace app\miniprogram\Model;


use app\miniprogram\common\Model;

class MiniProgramsFollower extends Model
{
    var $table = "mini_programs_follower";

    public function getFollowerByOpenId($openId)
    {
        $openId = (string)$openId;
        $model = new \app\miniprogram\common\Model\MiniProgramsFollower();
        $sql = "SELECT * from mini_programs_follower where openid = :openid  limit 1";
        $data = $model->db()->query($sql, ['openid' => $openId]);
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    public function getFollowerById($id)
    {
        $id = (string)$id;
        $model = new \app\miniprogram\common\Model\MiniProgramsFollower();
        $sql = "SELECT * from mini_programs_follower where id = :id  limit 1";
        $data = $model->db()->query($sql, ['id' => $id]);
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    public function getFollowerByUserKey($userKey,$appid)
    {
        $userKey = (string)$userKey;
        $model = new \app\miniprogram\common\Model\MiniProgramsFollower();
        $sql = "SELECT * from mini_programs_follower where curUserKey = :curUserKey and appid=:appid limit 1";
        $data = $model->db()->query($sql, ['curUserKey' => $userKey,"appid"=>$appid]);
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    public function getCountOfFollowers()
    {

        $model = new \app\miniprogram\common\Model\MiniProgramsFollower();
        $sql = " SELECT count(id) followercount from mini_programs_follower ";
        $data = $model->db()->query($sql);
        if( empty($data) ) {
            return [];
        }

        return $data[0];
    }

    public function getCurrentNewFollowers($appid,$time)
    {

        $model = new \app\miniprogram\common\Model\MiniProgramsFollower();
        $sql = " SELECT count(id) newfollower from mini_programs_follower WHERE followTime > :followTime and appid=:appid";
        $data = $model->db()->query($sql,["followTime"=>$time,"appid"=>$appid]);
        if( empty($data) ) {
            return [];
        }

        return $data[0];
    }

    public function getDurationNewFollowers($appid,$fromTime,$endTime)
    {

        $model = new \app\miniprogram\common\Model\MiniProgramsFollower();
        $sql = " SELECT count(id) newfollower from mini_programs_follower WHERE followTime > :fromTime and followTime < :endTime and appid=:appid";
        $data = $model->db()->query($sql,["fromTime"=>$fromTime,"endTime"=>$endTime,"appid"=>$appid]);
        if( empty($data) ) {
            return [];
        }

        return $data[0];
    }


    public static function getOneById($id)
    {
        return self::find($id);
    }
    public function getFollowerIdByNickNameAndOpenId($appid,$content){

        $sql = " SELECT id  from mini_programs_follower WHERE (nickName like :nickName or openid like :openid) and appid=:appid";
        $data = $this->query($sql,["nickName"=>"%".$content."%","openid"=>"%".$content."%","appid"=>$appid]);
        if( empty($data) ) {
            return [];
        }

        return $data;

    }
}

