<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/3/14
 * Time: 9:47
 */
namespace app\miniapp\model;


use app\common\Model;

class MiniProgramsSceneLog extends Model
{
	var $table = "mini_programs_scene_log";

	public function findByFollowerIdAndCreateTime($followerId,$createTime,$scene) {

		$mode = new \app\miniapp\model\MiniProgramsSceneLog();
		$sql = "SELECT * FROM `mini_programs_scene_log`  WHERE followerId=:followerId and createTime>=:createTime and scene=:scene ";
		$Info = $mode->db()->query($sql,["followerId"=>$followerId,"scene"=>$scene,"createTime"=>$createTime]);
		if( empty($Info) ) {
			return [];
		}

		return $Info;
	}

	public function findTodayAllVisitors($appid,$createTime) {

		$mode = new \app\miniapp\model\MiniProgramsSceneLog();
		$sql = "SELECT * FROM `mini_programs_scene_log`  WHERE  createTime>=:createTime and appid=:appid GROUP by followerId";
		$Info = $mode->db()->query($sql,["appid"=>$appid,"createTime"=>$createTime]);
		if( empty($Info) ) {
			return [];
		}

		return $Info;
	}

	public function findDurationAllVisitors($appid, $searchFrom, $searchTo) {
		$mode = new \app\miniapp\model\MiniProgramsSceneLog();
		$sql = "SELECT * FROM `mini_programs_scene_log`  WHERE  createTime>=:searchFrom and createTime<:searchTo and appid=:appid GROUP by followerId";
		$Info = $mode->db()->query($sql,["appid"=>$appid,"searchFrom"=>$searchFrom,"searchTo"=>$searchTo]);
		if( empty($Info) ) {
			return [];
		}

		return $Info;
	}


	public function findUserSceneInfoByDuration($appid, $from,$to,$searchContent="",$curSearchScene=0){

		$info = self::alias('s')->field('s.id,a.apptitle,f.openid,f.gender,f.nickname,s.createTime,s.pageUrl,s.scene')
				->join([["mini_programs_follower f","f.id=s.followerId"],["mini_programs_applist a","a.id=s.appid"]])
				->where("s.appid",$appid)->where("s.createTime",">=",$from)->where("s.createTime","<=",$to);

		if(($searchContent!=""||$searchContent!=0)&&($curSearchScene==0)){
			$info->where("f.nickName like :nickName or f.openid like :openid or s.scene = :scene")
					->bind(["nickName"=>"%".$searchContent."%","openid"=>"%".$searchContent."%","scene"=>$searchContent]);
		}else if(($searchContent!=""||$searchContent!=0)&&($curSearchScene!=0)){
				$info->where("s.scene", $curSearchScene)->where("f.nickName like :nickName or f.openid like :openid")
						->bind(["nickName" => "%" . $searchContent . "%", "openid" => "%" . $searchContent . "%"]);
		}

		$data = $info->order("s.id desc")->limit(100)->select();


		return json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}


	public function findUserSceneCountByDuration($appid, $from,$to,$followerId){

		$info = self::alias('s')->field('s.scene,f.nickName,s.createTime,s.followerId')
				->join([["mini_programs_follower f","f.id = s.followerId"],["mini_programs_applist a","a.id=s.appid"]])
				->where("s.appid",$appid)->where("s.createTime",">=",$from)->where("s.createTime","<=",$to);

		if(empty($followerId)){$followerId=0;}
			$info->where("s.followerId" ,"in" ,$followerId);



		$data = $info->order("s.id desc")->limit(100)->select();

		return json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	public function findCurSceneCountByDuration($appid, $from,$to,$followerId,$scene){

		$info = self::alias('s')->field('s.scene,f.nickName,s.createTime,s.followerId')
				->join([["mini_programs_follower f","f.id = s.followerId"],["mini_programs_applist a","a.id=s.appid"]])
				->where("s.appid",$appid)->where("s.scene",$scene)->where("s.createTime",">=",$from)->where("s.createTime","<=",$to);

			if(empty($followerId)){$followerId=0;}
			$info->where("s.followerId" ,"in" ,$followerId);



		$data = $info->order("s.id desc")->limit(100)->select();

		return json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}


	public function findTop5FollowerByDuration($appid, $from,$to,$followerId){
		$info = self::alias('s')->field(' count(*) countscene,s.followerId')
				->where("s.appid",$appid)->where("s.createTime",">=",$from)->where("s.createTime","<=",$to);

		if(empty($followerId)){$followerId=0;}
		$info->where("s.followerId" ,"in" ,$followerId);


		$data = $info->group("s.followerId")->order("countscene  desc")->limit(5)->select();

		return json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}



}