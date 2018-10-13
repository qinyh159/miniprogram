<?php
namespace app\miniapp\model;

use app\common\Model;

class MergerFollower extends Model {

	// https://github.com/catalyst8/Panmeta_Issues/issues/1244
	// 合并openid相同 curUserKey不同的用户
	public function byAppidOpenID($app_id,$openid,$curUserKey) {
		// curUserKey对应的关注用户
		$sql = "SELECT id,openid from mini_programs_follower where appid = :appid and curUserKey = :curUserKey limit 1";
		$follower = $this->query($sql,[ 'appid' => $app_id, 'curUserKey' => $curUserKey ]);

		if( empty($follower) ) {
			return false;
		}

		// 如果curUserKey对应的关注用户还没有OPENID,先填充OPENID
		if( empty($follower[0]['openid']) ) {
			$this->name('mini_programs_follower')->where('id',$follower[0]['id'])->update(['openid' => $openid]);
		}

		// openid相同的用户
		$sql = "SELECT id,curUserKey from mini_programs_follower where appid = :appid and openid = :openid order by id asc";
		$arrFollower = $this->query($sql,[ 'appid' => $app_id, 'openid' => $openid ]);

		// openid相同的用户少于两人,不需要往下执行
		if( !isset($arrFollower[1]['id']) ) {
			return false;
		}

		$fid = $this->fid = $arrFollower[0]['id'];//原始用户ID
		$time = $this->time = time();

		foreach ($arrFollower as $key => $row) {
			if( $key == 0 ) {
				continue;
			}

			$this->startTrans();
			$this->merge_table_log('mini_programs_qrcode_scan_log',$row);
			$this->merge_table_log('mini_programs_chat_message',$row);
			$this->merge_table_log('mini_programs_follower_location',$row);
			$this->merge_table_log('mini_programs_follower_mobile_info',$row);
			$this->merge_table_log('mini_programs_follower_networktype_info',$row);
			$this->merge_table_log('mini_programs_message',$row);
			// $this->merge_table_log('mini_programs_newmessage_record',$row);
			$this->merge_table_log('mini_programs_personal_detail',$row);
			$this->merge_table_log('mini_programs_scene_log',$row);

			$this->merge_spider($app_id,$row);
			$this->merge_spider_href($app_id,$row);

			$arrData = [
				'appid' => $app_id,
				'curUserKey' => $row['curUserKey'],
				'follower_id' => $row['id'],
				'follower_id_new' => $fid,
				'create_time' => $time,
			];
			$this->name('merge_mini_programs_follower')->insert($arrData);

			$sql = "INSERT into merge_mini_programs_follower_log select * from mini_programs_follower where id = :follower_id";
			$this->query($sql,[ 'follower_id' => $row['id'] ]);

			$sql = "DELETE from mini_programs_follower where id = :follower_id";
			$this->query($sql,[ 'follower_id' => $row['id'] ]);

			$this->commit();
		}

	}

	private function merge_table_log($table,$data = []) {
		$arrTable = ['mini_programs_qrcode_scan_log',
			'mini_programs_chat_message','mini_programs_follower_location','mini_programs_follower_mobile_info',
			'mini_programs_follower_networktype_info','mini_programs_message','mini_programs_newmessage_record',
			'mini_programs_personal_detail','mini_programs_scene_log'];

		$arrTable2 = ['mini_programs_chat_message','mini_programs_follower_location','mini_programs_follower_mobile_info',
			'mini_programs_follower_networktype_info','mini_programs_message','mini_programs_personal_detail',
			'mini_programs_scene_log'];

		if( !in_array($table, $arrTable) ) {
			return;
		}

		if( in_array($table, $arrTable2) ) {
			$sql = "SELECT id from $table where followerid = :follower_id";
		} else {
			$sql = "SELECT id from $table where follower_id = :follower_id";
		}
		
		$arrLog = $this->query($sql, ['follower_id' => $data['id']]);
		foreach ($arrLog as $key => $log) {
			$arrData = [
				'log_id' => $log['id'],
				'follower_id' => $data['id'],
				'follower_id_new' => $this->fid,
				'create_time' => $this->time,
			];
			$this->name('merge_'. $table)->insert($arrData);
		}

		if( in_array($table, $arrTable2) ) {
			$sql = "UPDATE $table set followerid = :fid where followerid = :follower_id";
		} else {
			$sql = "UPDATE $table set follower_id = :fid where follower_id = :follower_id";
		}
		
		$this->query($sql,['fid' => $this->fid,'follower_id' => $data['id']]);
	}

	private function merge_mini_programs_qrcode_scan_log($data = []) {
		$sql = "SELECT id from mini_programs_qrcode_scan_log where follower_id = :follower_id";
		$arrLog = $this->query($sql, ['follower_id' => $data['id']]);
		foreach ($arrLog as $key => $log) {
			$arrData = [
				'log_id' => $log['id'],
				'follower_id' => $data['id'],
				'follower_id_new' => $this->fid,
				'create_time' => $time,
			];
			$this->name('merge_qrcode_scan_log')->insert($arrData);
		}

		$sql = "UPDATE mini_programs_qrcode_scan_log set follower_id = :fid where follower_id = :follower_id";
		$this->query($sql,['fid' => $fid,'follower_id' => $data['id']]);
	}

	private function merge_spider($app_id,$data = []) {
		$errmsg = '';
		$follower_id_original = $this->fid;//原始用户ID
		$follower_id = $data['id'];//要合并的用户ID
		
		// 原始用户节点
		$sql = "SELECT * from w_analysis_network_node where follower_id = :follower_id and app_id = :app_id limit 1";
		$arrNodeOriginal = $this->query($sql, ['follower_id' => $follower_id_original, 'app_id' => $app_id]);
		if( empty($arrNodeOriginal) ) {
			$errmsg = '原始用户节点为空';
			return $errmsg;
		}
		$nodeOriginal = $arrNodeOriginal[0];

		// w_analysis_network_node 根据APPID FID 查找要合并的蜘蛛图节点
		// 添加节点合并记录
		// 要修改的节点
		$sql = "SELECT * from w_analysis_network_node where follower_id = :follower_id and app_id = :app_id limit 1";
		$arrNode = $this->query($sql, ['follower_id' => $follower_id, 'app_id' => $app_id]);
		if( empty($arrNode) ) {
			$errmsg = '要合并的用户节点为空';
			return $errmsg;
		}
		$node = $arrNode[0];
		$arrData = [
			'log_id' => $node['id'],
			'follower_id' => $follower_id,
			'follower_id_new' => $follower_id_original,
			'create_time' => $this->time,
		];
		$this->name('merge_w_analysis_network_node')->insert($arrData);

		// 备份要移除的节点数据到新表
		$sql = "INSERT into merge_w_analysis_network_node_log select * from w_analysis_network_node where id = :id";
		$this->query($sql,[ 'id' => $node['id'] ]);

		// 移除节点数据
		$sql = "DELETE from w_analysis_network_node where id = :id";
		$this->query($sql,[ 'id' => $node['id'] ]);

		// 把旧节点的统计数据累计到原始节点上
		//  LEVEL数据还没想好怎么处理
		$sql = "UPDATE w_analysis_network_node set affected_count = affected_count+ :affected_count,
			affected_total = affected_total + :affected_total,view_count = view_count + :view_count,
			forward_count = forward_count+ :forward_count where follower_id = :follower_id and app_id = :app_id";
		$this->query($sql,[
			'follower_id' => $follower_id_original,
			'app_id' => $app_id,
			'affected_count' => $node['affected_count'],
			'affected_total' => $node['affected_total'],
			'view_count' => $node['view_count'],
			'forward_count' => $node['forward_count'],
		]);

		// w_analysis_network_node_relation 节点关系 合并记录
		// $sql = "SELECT * from w_analysis_network_node_relation where parent_id = :parent_id and app_id = :app_id";
		// $arrRelation = $this->query($sql, ['parent_id' => $node['id'], 'app_id' => $app_id]);
		$sql = "SELECT * from w_analysis_network_node_relation where parent_id = :parent_id";
		$arrRelation = $this->query($sql, ['parent_id' => $node['id']]);
		if( empty($arrRelation) ) {
			return;
		}

		foreach ($arrRelation as $relation) {
			$arrData = [
				'node_id' => $relation['node_id'],
				'parent_id' => $relation['parent_id'],
				'parent_id_new' => $nodeOriginal['id'],
				'create_time' => $this->time,
			];
			$this->name('merge_w_analysis_network_node_relation')->insert($arrData);

			// // 备份要移除的节点数据到新表
			$sql = "INSERT into merge_w_analysis_network_node_relation_log select * from w_analysis_network_node_relation where node_id = :node_id and parent_id = :parent_id";
			$this->query($sql,[ 'node_id' => $relation['node_id'],'parent_id' => $relation['parent_id'] ]);

			// 移除旧的关联数据
			$sql = "DELETE from w_analysis_network_node_relation where node_id = :node_id and parent_id = :parent_id";
			$this->query($sql,[ 'node_id' => $relation['node_id'],'parent_id' => $relation['parent_id'] ]);

			// 新建新的关联数据,如果插入失败,说明已存在新节点,没有影响
			$arrData = [
				'node_id' => $relation['node_id'],
				'parent_id' => $nodeOriginal['id'],
				'app_id' => $app_id,
			];

			$this->name('w_analysis_network_node_relation')->insert($arrData);
		}
		
	}

	private function merge_spider_href($app_id,$data = []) {
		$errmsg = '';
		$follower_id_original = $this->fid;//原始用户ID
		$follower_id = $data['id'];//要合并的用户ID
		
		// w_analysis_network_href_node 根据 要合并的用户ID 查找要合并的蜘蛛图节点
		// 添加节点合并记录
		// 要修改的节点
		$sql = "SELECT * from w_analysis_network_href_node where follower_id = :follower_id";
		$arrNode = $this->query($sql, ['follower_id' => $follower_id]);
		if( empty($arrNode) ) {
			$errmsg = '要合并的用户节点为空';
			return $errmsg;
		}

		foreach ($arrNode as $node) {
			$href_id = $node['href_id'];

			// 原始用户节点
			$sql = "SELECT * from w_analysis_network_href_node where follower_id = :follower_id and href_id = :href_id limit 1";
			$arrNodeOriginal = $this->query($sql, ['follower_id' => $follower_id_original, 'href_id' => $href_id]);
			if( empty($arrNodeOriginal) ) {
				$errmsg = '原始用户节点为空';
				return $errmsg;
			}
			$nodeOriginal = $arrNodeOriginal[0];

			$arrData = [
				'log_id' => $node['id'],
				'follower_id' => $follower_id,
				'follower_id_new' => $follower_id_original,
				'create_time' => $this->time,
			];
			$this->name('merge_w_analysis_network_href_node')->insert($arrData);

			// 备份要移除的节点数据到新表
			$sql = "INSERT into merge_w_analysis_network_href_node_log select * from w_analysis_network_href_node where id = :id";
			$this->query($sql,[ 'id' => $node['id'] ]);

			// 移除节点数据
			$sql = "DELETE from w_analysis_network_href_node where id = :id";
			$this->query($sql,[ 'id' => $node['id'] ]);

			// 把旧节点的统计数据累计到原始节点上
			//  LEVEL数据还没想好怎么处理
			$sql = "UPDATE w_analysis_network_href_node set affected_count = affected_count+ :affected_count,
				affected_total = affected_total + :affected_total,view_count = view_count + :view_count,
				forward_count = forward_count+ :forward_count where follower_id = :follower_id and href_id = :href_id";
			$this->query($sql,[
				'follower_id' => $follower_id_original,
				'href_id' => $href_id,
				'affected_count' => $node['affected_count'],
				'affected_total' => $node['affected_total'],
				'view_count' => $node['view_count'],
				'forward_count' => $node['forward_count'],
			]);

			// w_analysis_network_node_relation 节点关系 合并记录
			$sql = "SELECT * from w_analysis_network_href_node_relation where parent_id = :parent_id";
			$arrRelation = $this->query($sql, ['parent_id' => $node['id']]);
			if( empty($arrRelation) ) {
				return;
			}

			foreach ($arrRelation as $relation) {
				$arrData = [
					'node_id' => $relation['node_id'],
					'parent_id' => $relation['parent_id'],
					'parent_id_new' => $nodeOriginal['id'],
					'create_time' => $this->time,
				];
				$this->name('merge_w_analysis_network_href_node_relation')->insert($arrData);

				// // 备份要移除的节点数据到新表
				$sql = "INSERT into merge_w_analysis_network_href_node_relation_log select * from w_analysis_network_href_node_relation where node_id = :node_id and parent_id = :parent_id";
				$this->query($sql,[ 'node_id' => $relation['node_id'],'parent_id' => $relation['parent_id'] ]);

				// 移除旧的关联数据
				$sql = "DELETE from w_analysis_network_href_node_relation where node_id = :node_id and parent_id = :parent_id";
				$this->query($sql,[ 'node_id' => $relation['node_id'],'parent_id' => $relation['parent_id'] ]);

				// 新建新的关联数据,如果插入失败,说明已存在新节点,没有影响
				$arrData = [
					'node_id' => $relation['node_id'],
					'parent_id' => $nodeOriginal['id'],
					'href_id' => $href_id,
				];

				$this->name('w_analysis_network_href_node_relation')->insert($arrData);
			}
		}
		
	}
}

