<?php

namespace app\miniapp\model;


use app\common\Model;

class Qrcode extends Model {

	public function __construct() {
		parent::__construct();
	}

	public function getQrcodeName($query = []) {
		$query['name'] = trim($query['name']);
		$query['app_id'] = intval($query['app_id']);
		$sql = "SELECT id from mini_programs_qrcode where app_id = :app_id and name = :name limit 1";
		$data = $this->query($sql, $query);
		return $data;
	}

	public function getQrcode($query = []) {
		$limit = 20;
		$offset = false;
		if( isset($query['limit']) ) $limit = intval($query['limit']);
		if( isset($query['offset']) ) $offset = intval($query['offset']);

		$arrField = ['id','group_id','removed','name_page','app_id','scene'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['id']) ) {
			$query['id'] = intval($query['id']);
			$where[] = ' and q.id = :id ';
		}

		if( isset($query['app_id']) ) {
			$query['app_id'] = intval($query['app_id']);
			$where[] = ' and q.app_id = :app_id ';
		}

		if( isset($query['scene']) ) {
			$query['scene'] = trim($query['scene']);
			$where[] = ' and q.scene = :scene ';
		}

		if( isset($query['group_id']) ) {
			$query['group_id'] = intval($query['group_id']);
			$where[] = ' and q.group_id = :group_id ';
		}

		if( isset($query['removed']) ) {
			$query['removed'] = intval($query['removed']);
			$where[] = ' and q.removed = :removed ';
		}

		if( isset($query['name']) ) {
			$query['name'] = '%'.trim($query['name']).'%';
			$where[] = ' and q.name like :name ';
		}

		if( isset($query['page']) ) {
			$query['page'] = '%'.trim($query['page']).'%';
			$where[] = ' and q.page like :page ';
		}

		if( isset($query['name_page']) ) {
			$query['name_page1'] = '%'.trim($query['name_page']).'%';
			$query['name_page2'] = '%'.trim($query['name_page']).'%';
			$where[] = ' and (q.name like :name_page1 or q.page like :name_page2) ';
			unset($query['name_page']);
		}

		$sql = "SELECT q.*,g.name group_name FROM mini_programs_qrcode q
		join mini_programs_qrcode_group g on g.id = q.group_id
		where 1 " . implode(' ', $where) . ' order by q.id desc ';

		if( $offset !== false ) {
			$sql .= " limit $limit offset $offset";
		}
		$data = $this->query($sql, $query);
		return $data;
	}

	public function countQrcode($query = []) {
		$arrField = ['id','group_id','type','removed','name','page','app_id','scene'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['id']) ) {
			$query['id'] = intval($query['id']);
			$where[] = ' and q.id = :id ';
		}

		if( isset($query['app_id']) ) {
			$query['app_id'] = intval($query['app_id']);
			$where[] = ' and q.app_id = :app_id ';
		}

		if( isset($query['type']) ) {
			$query['type'] = intval($query['type']);
			$where[] = ' and q.type = :type ';
		}

		if( isset($query['scene']) ) {
			$query['scene'] = trim($query['scene']);
			$where[] = ' and q.scene = :scene ';
		}

		if( isset($query['group_id']) ) {
			$query['group_id'] = intval($query['group_id']);
			$where[] = ' and q.group_id = :group_id ';
		}

		if( isset($query['removed']) ) {
			$query['removed'] = intval($query['removed']);
			$where[] = ' and q.removed = :removed ';
		}

		if( isset($query['name']) ) {
			$query['name'] = trim($query['name']);
			$where[] = ' and q.name = :name ';
		}

		if( isset($query['page']) ) {
			$query['page'] = trim($query['page']);
			$where[] = ' and q.page = :page ';
		}


		$sql = "SELECT count(*) count FROM mini_programs_qrcode q
		join mini_programs_qrcode_group g on g.id = q.group_id
		where 1 " . implode(' ', $where);

		$data = $this->query($sql, $query);
		return isset($data[0]) ? array_pop($data[0]) : 0;
	}



	public function getAnalysisAppById($id) {
		$id = intval($id);
		$sql = "SELECT * from w_analysis_app where id = $id";
		$data = $this->query($sql);
		return array_pop($data);
	}

	public function getDaySummary($query = []) {
		$limit = 20;
		$offset = false;
		if( isset($query['limit']) ) $limit = intval($query['limit']);
		if( isset($query['offset']) ) $offset = intval($query['offset']);

		$arrField = ['id','qrcode_id','type','group_id','stime','etime'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['id']) ) {
			$query['id'] = intval($query['id']);
			$where[] = ' and s.id = :id ';
		}

		if( isset($query['qrcode_id']) ) {
			$query['qrcode_id'] = trim($query['qrcode_id']);
			$where[] = ' and s.qrcode_id = :qrcode_id ';
		}

		if( isset($query['type']) ) {
			$query['type'] = trim($query['type']);
			$where[] = ' and q.type = :type ';
		}

		if( isset($query['group_id']) ) {
			$query['group_id'] = trim($query['group_id']);
			$where[] = ' and q.group_id = :group_id ';
		}

		if( isset($query['stime']) ) {
			$query['stime'] = trim($query['stime']);
			$where[] = ' and :stime <= s.create_time ';
		}

		if( isset($query['etime']) ) {
			$query['etime'] = trim($query['etime']);
			$where[] = ' and s.create_time < :etime ';
		}


		$sql = "SELECT s.*,q.name,q.page FROM mini_programs_qrcode_scan_day_summary s
		join mini_programs_qrcode q on q.id = s.qrcode_id
		where 1 " . implode(' ', $where);

		if( $offset !== false ) {
			$sql .= " offset $offset limit $limit";
		}
		
		$data = $this->query($sql, $query);
		return $data;
	}

	public function countDaySummary($query = []) {
		$arrField = ['id','qrcode_id','type','group_id','stime','etime'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['id']) ) {
			$query['id'] = intval($query['id']);
			$where[] = ' and s.id = :id ';
		}

		if( isset($query['qrcode_id']) ) {
			$query['qrcode_id'] = trim($query['qrcode_id']);
			$where[] = ' and s.qrcode_id = :qrcode_id ';
		}

		if( isset($query['type']) ) {
			$query['type'] = trim($query['type']);
			$where[] = ' and q.type = :type ';
		}

		if( isset($query['group_id']) ) {
			$query['group_id'] = trim($query['group_id']);
			$where[] = ' and q.group_id = :group_id ';
		}

		if( isset($query['stime']) ) {
			$query['stime'] = trim($query['stime']);
			$where[] = ' and :stime <= s.create_time ';
		}

		if( isset($query['etime']) ) {
			$query['etime'] = trim($query['etime']);
			$where[] = ' and s.create_time < :etime ';
		}


		$sql = "SELECT count(*) FROM mini_programs_qrcode_scan_day_summary s
		join mini_programs_qrcode q on q.id = s.qrcode_id
		where 1 " . implode(' ', $where);

		$data = $this->query($sql, $query);
		return isset($data[0]) ? array_pop($data[0]) : 0;
	}

	public function sumDaySummary($query = []) {
		$arrField = ['id','qrcode_id','type','group_id','stime','etime'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['id']) ) {
			$query['id'] = intval($query['id']);
			$where[] = ' and s.id = :id ';
		}

		if( isset($query['qrcode_id']) ) {
			$query['qrcode_id'] = trim($query['qrcode_id']);
			$where[] = ' and s.qrcode_id = :qrcode_id ';
		}

		if( isset($query['type']) ) {
			$query['type'] = trim($query['type']);
			$where[] = ' and q.type = :type ';
		}

		if( isset($query['group_id']) ) {
			$query['group_id'] = trim($query['group_id']);
			$where[] = ' and q.group_id = :group_id ';
		}

		if( isset($query['stime']) ) {
			$query['stime'] = trim($query['stime']);
			$where[] = ' and :stime <= s.create_time ';
		}

		if( isset($query['etime']) ) {
			$query['etime'] = trim($query['etime']);
			$where[] = ' and s.create_time < :etime ';
		}


		$sql = "SELECT sum(s.users) users,sum(s.times) times,sum(s.new_users) new_users,s.create_time FROM mini_programs_qrcode_scan_day_summary s
		join mini_programs_qrcode q on q.id = s.qrcode_id
		where 1 " . implode(' ', $where) .
		' group by create_time';

		$data = $this->query($sql, $query);
		return $data;
	}

	public function sumSummary($query = []) {
		$arrField = ['id','qrcode_id','type','page','group_id','stime','etime'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['id']) ) {
			$query['id'] = intval($query['id']);
			$where[] = ' and s.id = :id ';
		}

		if( isset($query['qrcode_id']) ) {
			$query['qrcode_id'] = trim($query['qrcode_id']);
			$where[] = ' and s.qrcode_id = :qrcode_id ';
		}

		if( isset($query['type']) ) {
			$query['type'] = trim($query['type']);
			$where[] = ' and q.type = :type ';
		}

		if( isset($query['page']) ) {
			$query['page'] = trim($query['page']);
			$where[] = ' and q.page = :page ';
		}

		if( isset($query['group_id']) ) {
			$query['group_id'] = trim($query['group_id']);
			$where[] = ' and q.group_id = :group_id ';
		}

		if( isset($query['stime']) ) {
			$query['stime'] = trim($query['stime']);
			$where[] = ' and :stime <= s.create_time ';
		}

		if( isset($query['etime']) ) {
			$query['etime'] = trim($query['etime']);
			$where[] = ' and s.create_time < :etime ';
		}


		$sql = "SELECT sum(s.users) users,sum(s.times) times,sum(s.new_users) new_users,s.create_time FROM mini_programs_qrcode_scan_day_summary s
		join mini_programs_qrcode q on q.id = s.qrcode_id
		where 1 " . implode(' ', $where) ;
		$data = $this->query($sql, $query);
		return $data;
	}

	public function sumQrcodeSummary($query = []) {
		$arrField = ['id','qrcode_id','page','type','group_id','stime','etime'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['id']) ) {
			$query['id'] = intval($query['id']);
			$where[] = ' and s.id = :id ';
		}

		if( isset($query['qrcode_id']) ) {
			$query['qrcode_id'] = trim($query['qrcode_id']);
			$where[] = ' and s.qrcode_id = :qrcode_id ';
		}

		if( isset($query['type']) ) {
			$query['type'] = trim($query['type']);
			$where[] = ' and q.type = :type ';
		}

		if( isset($query['page']) ) {
			$query['page'] = trim($query['page']);
			$where[] = ' and q.page = :page ';
		}

		if( isset($query['group_id']) ) {
			$query['group_id'] = trim($query['group_id']);
			$where[] = ' and q.group_id = :group_id ';
		}

		if( isset($query['stime']) ) {
			$query['stime'] = trim($query['stime']);
			$where[] = ' and :stime <= s.create_time ';
		}

		if( isset($query['etime']) ) {
			$query['etime'] = trim($query['etime']);
			$where[] = ' and s.create_time < :etime ';
		}

		$sql = "SELECT q.id,sum(s.users) users,sum(s.times) times,sum(s.new_users) new_users,s.create_time,q.name,q.page,q.scene FROM mini_programs_qrcode_scan_day_summary s
		join mini_programs_qrcode q on q.id = s.qrcode_id
		where 1 " . implode(' ', $where) .
		' group by s.qrcode_id';

		$data = $this->query($sql, $query);
		return $data;
	}


}

