<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/3/14
 * Time: 0:51
 */
namespace app\miniapp\model;


use app\common\Model;

class MiniProgramsSceneDaySummary extends Model
{
	var $table = "mini_programs_scene_day_summary";

	public function findByDateAndScene($appid, $scene, $date)
	{

		$mode = new \app\miniapp\model\MiniProgramsSceneDaySummary();
		$sql = "SELECT * FROM `mini_programs_scene_day_summary`  WHERE appid=:appid and createTime=:date and scene=:scene ";
		$Info = $mode->db()->query($sql, ["appid" => $appid, "scene" => $scene, "date" => $date]);
		if (empty($Info)) {
			return [];
		}

		return $Info;
	}

	public function insertSceneDaySummary($summary)
	{
		$Mode = new \app\miniapp\model\MiniProgramsSceneDaySummary();
		return $Mode->name("mini_programs_scene_day_summary")->insertGetId($summary);
	}

	public function updateSceneDaySummary($summary, $newUser, $logFlag)
	{
		$Mode = new \app\miniapp\model\MiniProgramsSceneDaySummary();
		//当前有新用户
		if ($newUser) {
			$Mode->db()->where("id", $summary[0]["id"])->update(
				[
					"users" => $summary[0]["users"] + 1,
					"newUsers" => $summary[0]["newUsers"] + 1,
					"times" => $summary[0]["times"] + 1
				]
			);
		} else {
			//以某个场景值登录过
			if ($logFlag) {
				$Mode->db()->where("id", $summary[0]["id"])->update(
					[
						"times" => $summary[0]["times"] + 1
					]
				);
			} else {
				$Mode->db()->where("id", $summary[0]["id"])->update(
					[
						"users" => $summary[0]["users"] + 1,
						"times" => $summary[0]["times"] + 1
					]
				);
			}

		}

	}

	public function findSceneByDuration($appid, $from,$to,$scene=0){
		$mode = new \app\miniapp\model\MiniProgramsSceneDaySummary();
		$startSql = "SELECT s.id,s.appid,s.scene,s.users,s.newUsers,s.times,s.createTime,a.appid,a.apptitle FROM `mini_programs_scene_day_summary`  s JOIN mini_programs_applist a on s.appid = a.id WHERE s.appid=:appid and s.createTime>=:from and s.createTime<=:to  ";
		$param["appid"] = $appid;
		$param["from"] = $from;
		$param["to"] = $to;
		$sceneSql = "";
		if($scene!=0){
			$sceneSql = " and s.scene = :scene ";
			$param["scene"] = $scene;
		}
		$endSql = " ORDER BY  s.users desc";
		$sql = $startSql.$sceneSql.$endSql;

		$Info = $mode->db()->query($sql, $param);
		if (empty($Info)) {
			return [];
		}

		foreach ($Info as $row) {

			if( isset($result[ $row['scene']]) ) {
				$result[ $row['scene'] ]['data'][ $row['createTime'] ] = $row['users'];
			} else {
				$result[ $row['scene']] = [
						'name' => 	$row['scene'],
						'data' =>[ $row['createTime'] => $row['users']]
				];
			}
		}

		return $result;
	}





	public function findByDate($appid,$starDate, $endDate)
	{

		$mode = new \app\miniapp\model\MiniProgramsSceneDaySummary();
		$sql = "SELECT s.id,s.appid,s.scene,s.users,s.newUsers,s.times,s.createTime,a.appid,a.apptitle FROM `mini_programs_scene_day_summary`  s JOIN mini_programs_applist a on s.appid = a.id WHERE s.appid=:appid and s.createTime BETWEEN :starDate AND :endDate ORDER BY  s.users desc";
		$Info = $mode->db()->query($sql, ["appid" => $appid, "starDate" => $starDate,"endDate" => $endDate]);
		if (empty($Info)) {
			return [];
		}

		return $Info;
	}

	public function findSummaryInDuration($appid, $searchFrom, $searchTo, $scene)
	{

		$mode = new \app\miniapp\model\MiniProgramsSceneDaySummary();
		$startSql = "SELECT s.id,s.appid,s.scene,sum(s.users) users,sum(s.newUsers) newUsers,sum(s.times) times,s.createTime,a.appid,a.apptitle FROM `mini_programs_scene_day_summary`  s JOIN mini_programs_applist a on s.appid = a.id
			WHERE s.appid=:appid and s.createTime>=:searchFrom  and s.createTime<:searchTo ";
		$param = array();
		if (!empty($scene)) {
			$sceneSql = " and s.scene=:scene ";
			$param["scene"] = $scene;
		}
		$endSql = " GROUP BY scene ORDER BY  users desc";
		$sceneSql = empty($sceneSql) ? "" : $sceneSql;
		$sql = $startSql .$sceneSql . $endSql;
		$param["appid"] = $appid;
		$param["searchFrom"] = $searchFrom;
		$param["searchTo"] = $searchTo;

		$Info = $mode->db()->query($sql, $param);


		if (empty($Info)) {
			return [];
		}

		return $Info;
	}

	public function findNewVisitors($appid, $date)
	{

		$mode = new \app\miniapp\model\MiniProgramsSceneDaySummary();
		$sql = "SELECT sum(newUsers) newUsers FROM `mini_programs_scene_day_summary`   WHERE appid=:appid and createTime=:date  ";
		$Info = $mode->db()->query($sql, ["appid" => $appid, "date" => $date]);
		if (empty($Info)) {
			return [];
		}

		return $Info;
	}

}
