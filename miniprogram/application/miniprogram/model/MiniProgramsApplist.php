<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/1/10
 * Time: 9:56
 */
namespace app\miniprogram\model;


use app\miniprogram\common\Model;

class MiniProgramsApplist extends Model {
	var $table = "mini_programs_applist";

	public function getAppById($id) {
        return self::find($id);
	}

	public function getAppByUniqueId($uniqueid) {

		$mode = new MiniProgramsApplist();
		$sql = "SELECT * FROM `mini_programs_applist`  WHERE  uniqueid=:uniqueid";
		$Info = $mode->db()->query($sql, ['uniqueid' => $uniqueid]);
		if( empty($Info) ) {
			return [];
		}

		return $Info[0];
	}

	public function getAllAppList() {

		$mode = new MiniProgramsApplist();
		$sql = "SELECT * FROM `mini_programs_applist`  group by  uniqueid order by id ASC ";
		$Info = $mode->db()->query($sql);
		if( empty($Info) ) {
			return [];
		}

		return $Info;
	}

	public function getApp($query = []) {
		$limit = 20;
		$offset = 0;
		if( isset($query['limit']) ) $limit = intval($query['limit']);
		if( isset($query['offset']) ) $offset = intval($query['offset']);

		$arrField = ['id','group_id','appid','apptitle','uniqueid'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['group_id']) ) {
			$query['group_id'] = intval($query['group_id']);
			$where[] = ' and group_id = :group_id ';
		}

		if( isset($query['appid']) ) {
			$query['appid'] = intval($query['appid']);
			$where[] = ' and appid = :appid ';
		}

		if( isset($query['apptitle']) ) {
			$query['apptitle'] = trim($query['apptitle']);
			$where[] = ' and apptitle = :apptitle ';
		}

		if( isset($query['uniqueid']) ) {
			$query['uniqueid'] = trim($query['uniqueid']);
			$where[] = ' and uniqueid = :uniqueid ';
		}


		$sql = "SELECT * FROM mini_programs_applist where 1 " . implode(' ', $where);

		if( $offset ) {
			$sql .= " offset $offset limit $limit";
		}
		
		$data = $this->query($sql, $query);
		return $data;
	}

	public function getAppByOriginalid($originalid) {

		$mode = new MiniProgramsApplist();
		$sql = "SELECT * FROM `mini_programs_applist`  WHERE  originalid=:originalid";
		$Info = $mode->db()->query($sql, ['originalid' => $originalid]);
		if( empty($Info) ) {
			return [];
		}

		return $Info[0];
	}

    public static function getOneById($appid)
    {
        $result = self::find($appid);
        return $result;
	}

    public static function getOneByUniqueId($uniqueid)
    {
        $result = self::where(['uniqueid' => $uniqueid])->find();
        return $result;
	}

}
