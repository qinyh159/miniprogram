<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2017/11/22
 * Time: 17:42
 */
namespace app\miniapp\model;


use app\common\Model;

class MiniProgramsEvent extends Model
{
    var $table = "mini_programs_event";


    public function getSceneGroupByAppId($appid,$time) {

        $appid = (int)$appid;
        $time = (int)$time;
        $mode = new \app\miniapp\model\MiniProgramsEvent();
        $sql = "SELECT count(id) count, scene ,appid  FROM `mini_programs_event`  WHERE  appid=:appid and scene!=0 and event_timestamp>:time  group by scene  order by count desc limit 15";
        $Info = $mode->db()->query($sql, ['appid' => $appid,"time"=>$time]);
        if( empty($Info) ) {
            return [];
        }

        return $Info;
    }

    public function getTodayScene($time,$appid) {
        $time = (int)$time;
        $mode = new \app\miniapp\model\MiniProgramsEvent();
        $time = $time*1000;
        $sql = "SELECT scene FROM `mini_programs_event` WHERE scene !=0 and event_timestamp>:time and appid = :appid group by scene";
        $Info = $mode->db()->query($sql, ["time"=>$time,"appid"=>$appid]);
        if( empty($Info) ) {
            return [];
        }

        return $Info;
    }

    public function getTodayAllVisitor($time,$scene,$appid) {
        $time = (int)$time;
        $scene = (int)$scene;
        $appid = (int)$appid;
        $mode = new \app\miniapp\model\MiniProgramsEvent();
        $time = $time*1000;
        $sql = "SELECT followerid FROM `mini_programs_event` WHERE scene =:scene and event_timestamp>:time and appid=:appid group by followerid";
        $Info = $mode->db()->query($sql, ["time"=>$time,"scene"=>$scene,"appid"=>$appid]);
        if( empty($Info) ) {
            return [];
        }

        return $Info;
    }

    public function getTodayNewVisitor($time,$scene,$appid) {
        $time = (int)$time;
        $scene = (int)$scene;
        $appid = (int)$appid;
        $mode = new \app\miniapp\model\MiniProgramsEvent();
        $sql = "SELECT * FROM mini_programs_follower f JOIN `mini_programs_event` e on f.followTime > :followTime WHERE e.scene =:scene and f.appid = :appid and e.event_timestamp>:eventTime and f.id=e.followerid group by f.id";
        $Info = $mode->db()->query($sql, ["followTime"=>$time,"scene"=>$scene,"eventTime"=>$time*1000,"appid"=>$appid]);
        if( empty($Info) ) {
            return [];
        }

        return $Info;
    }

    public function getCountOfFollowers($appid,$time)
    {
        $time = (int)$time;
        $appid = (int)$appid;

        $model = new \app\miniapp\model\MiniProgramsFollower();
        $sql = " SELECT id  from mini_programs_event WHERE event_timestamp>:time and scene!=0 and appid=:appid GROUP by openid";
        $data = $model->db()->query($sql,["time"=>$time,"appid"=>$appid]);
        if( empty($data) ) {
            return [];
        }
        return $data;
    }


    public function getDurationCountOfFollowers($appid,$startTime,$endTime,$searchContent)
    {
        $startTime = (int)$startTime;
        $endTime = (int)$endTime;
        $appid = (int)$appid;
        $param = ["startTime"=>$startTime,"endTime"=>$endTime,"appid"=>$appid];
        $startSql = "SELECT id  from mini_programs_event WHERE event_timestamp>:startTime and event_timestamp<:endTime and scene!=0 ";
        if(!empty($searchContent)&&$searchContent!=0){
            $contentSql = " and scene=:content ";
            $param["content"]= $searchContent;
        }
        $endSql = " and appid=:appid GROUP by openid";

        $model = new \app\miniapp\model\MiniProgramsFollower();
        $contentSql = empty($contentSql)?" ":$contentSql;
        $sql = $startSql.$contentSql.$endSql;

        $data = $model->db()->query($sql,$param);
        if( empty($data) ) {
            return [];
        }
        return $data;
    }

    public function getDurationScene($appid,$startTime,$endTime,$searchContent) {
        $startTime = (int)$startTime;
        $endTime = (int)$endTime;
        $appid = (int)$appid;
        $mode = new \app\miniapp\model\MiniProgramsEvent();
        $startTime = $startTime*1000;
        $endTime = $endTime*1000;
        $param = ["startTime"=>$startTime,"endTime"=>$endTime,"appid"=>$appid];
        $startSql = "SELECT scene FROM `mini_programs_event` WHERE scene !=0 and event_timestamp>:startTime and event_timestamp<:endTime  ";
        if(!empty($searchContent)&&$searchContent!=0){
            $contentSql = " and scene=:content ";
            $param["content"]= $searchContent;
        }
        $endSql = " and appid=:appid group by scene";
        $contentSql = empty($contentSql)?" ":$contentSql;
        $sql = $startSql.$contentSql.$endSql;

        $Info = $mode->db()->query($sql, $param);
        if( empty($Info) ) {
            return [];
        }

        return $Info;
    }


    public function getDurationAllVisitor($startTime,$endTime,$searchContent,$appid) {
        $startTime = (int)$startTime;
        $endTime = (int)$endTime;
        $appid = (int)$appid;
        $mode = new \app\miniapp\model\MiniProgramsEvent();
        $startTime = $startTime*1000;
        $endTime = $endTime*1000;
        $param = ["startTime"=>$startTime,"endTime"=>$endTime,"appid"=>$appid];
        $startSql = "SELECT followerid FROM `mini_programs_event` WHERE   event_timestamp>:startTime and event_timestamp<:endTime ";
        if(!empty($searchContent)&&$searchContent!=0){
            $contentSql = " and scene=:content ";
            $param["content"]= $searchContent;
        }
        $endSql = " and appid=:appid group by followerid";
        $contentSql = empty($contentSql)?" ":$contentSql;
        $sql = $startSql.$contentSql.$endSql;

        $Info = $mode->db()->query($sql, $param);
        if( empty($Info) ) {
            return [];
        }

        return $Info;
    }

    public function getDurationNewVisitor($startTime,$endTime,$searchContent,$appid) {
        $startTime = (int)$startTime;
        $endTime = (int)$endTime;
        $appid = (int)$appid;
        $mode = new \app\miniapp\model\MiniProgramsEvent();
        $startTime = $startTime*1000;
        $endTime = $endTime*1000;
        $param = ["startTime"=>$startTime,"endTime"=>$endTime,"appid"=>$appid,"startTime1"=>$startTime,"endTime1"=>$endTime];
        $startSql = "SELECT * FROM mini_programs_follower f JOIN `mini_programs_event` e on f.followTime > :startTime WHERE  f.followTime < :endTime ";
        if(!empty($searchContent)&&$searchContent!=0){
            $contentSql = " and scene=:content ";
            $param["content"]= $searchContent;
        }
        $endSql = " and f.appid = :appid and e.event_timestamp>:startTime1 and e.event_timestamp<:endTime1 and f.id=e.followerid group by f.id";
        $sql = $startSql.$contentSql.$endSql;
        $Info = $mode->db()->query($sql, $param);
        if( empty($Info) ) {
            return [];
        }

        return $Info;
    }

}
