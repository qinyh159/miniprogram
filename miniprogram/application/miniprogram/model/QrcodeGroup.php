<?php

namespace app\miniprogram\Model;


use app\miniprogram\common\Model;

class QrcodeGroup extends Model {

	public function __construct() {
		parent::__construct();
	}

	public function getGroup($query = []) {
		$limit = 20;
		$offset = 0;
		if( !empty($query['limit']) ) $limit = intval($query['limit']);
		if( !empty($query['offset']) ) $offset = intval($query['offset']);

		$arrField = ['id','removed','name'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['id']) ) {
			$query['id'] = intval($query['id']);
			$where[] = ' and id = :id ';
		}

		if( isset($query['removed']) ) {
			$query['removed'] = intval($query['removed']);
			$where[] = ' and removed = :removed ';
		}

		if( isset($query['name']) ) {
			$query['name'] = '%'.trim($query['name']).'%';
			$where[] = ' and name like :name ';
		}

		$sql = "SELECT * FROM mini_programs_qrcode_group where 1 " . implode(' ', $where). " order by id desc";;

		if( $limit ) {
			$sql .= " limit $limit offset $offset";
		}
		$data = $this->query($sql, $query);
		return $data;
	}

	public function getGroupLog($query = []) {
		$limit = 20;
		$offset = 0;
		if( isset($query['limit']) ) $limit = intval($query['limit']);
		if( isset($query['offset']) ) $offset = intval($query['offset']);

		$arrField = ['id','removed','name','group_id'];
		foreach ($query as $key => $value) {
			if( !in_array($key, $arrField) ) {
				unset($query[$key]);
			}
		}

		$where = [];

		if( isset($query['removed']) ) {
			$query['removed'] = intval($query['removed']);
			$where[] = ' and g.removed = :removed ';
		}

		if( isset($query['name']) ) {
			$query['name'] = trim($query['name']);
			$where[] = ' and g.name = :name ';
		}

		if( isset($query['group_id']) ) {
			$query['group_id'] = trim($query['group_id']);
			$where[] = ' and g.id = :group_id ';
		}

		$sql = "SELECT * FROM mini_programs_qrcode_group g
		join mini_programs_qrcode_group_log l on l.group_id = g.id 
		join mini_programs_qrcode q on q.id = l.qrcode_id 
		where 1 " . implode(' ', $where);

		if( $limit ) {
			$sql .= " limit $limit offset $offset";
		}
		
		$data = $this->query($sql, $query);
		return $data;
	}

	public function incCodes($id) {
		$sql = "UPDATE mini_programs_qrcode_group set codes = codes + 1 where id = :id";
		$this->execute($sql,['id' => $id]);
		// return $this->getNumRows();
	}

	public function decCodes($id) {
		$sql = "UPDATE mini_programs_qrcode_group set codes = codes - 1 where id = :id and codes > 0";
		$this->execute($sql,['id' => $id]);
		// return $this->getNumRows();
	}

}

