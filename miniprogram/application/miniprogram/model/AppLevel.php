<?php

namespace app\miniapp\model;


use app\common\Model;

class AppLevel extends Model {

	CONST TYPE_READ = 0;
	CONST TYPE_FORWARD = 1;

	public $protection_stack = [];

	public function __construct() {
		parent::__construct();
	}

	public function dbprefix($table) {
		return 'w_'. $table;
	}

	public function findNode($app_id, $query = NULL) {
		$where = $inner = $nest = array();

		$where[] = 'nn.`app_id`= ' . intval($app_id);
		$nest[] = '`app_id`= ' . intval($app_id);

		if (isset($query['mode']) && isset($query['affected']) && ($mode = trim($query['mode'])) && ($affected = intval($query['affected']))) {
			if ($mode == 'total') {
				$nest[] = '`affected_total` >= ' . $affected;
			} elseif ($mode == 'near') {
				$nest[] = '`affected_count` >= ' . $affected;
			}
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT nn.`id`,nn.`app_id`,nn.`follower_id`,f.`openid`, f.`nickname`,f.`avatarUrl`, '
				. 'nh.`affected_count`,nh.`affected_total`,nh.`view_count`,nh.`forward_count`, nh.`type`, nh.`time` '
				. ' FROM `' . $this->dbprefix('analysis_network_node') . '` nn'
				. ' INNER JOIN `mini_programs_follower` f ON f.`id` = nn.`follower_id`'
				. ' INNER JOIN ('
				. '     SELECT MAX(`id`) AS `id`, `node_id` FROM `' . $this->dbprefix('analysis_network_node_history') . '`'
				. '     GROUP BY `node_id` ORDER BY NULL) AS mnh ON mnh.`node_id`=nn.`id`'
				. ' INNER JOIN `' . $this->dbprefix('analysis_network_node_history') . '` nh ON nh.`id`=mnh.`id`'
				. ' INNER JOIN `' . $this->dbprefix('analysis_network_node_level') . '` nl ON nl.`parent_id`=nn.`id`'
				. ' INNER JOIN ('
				. '    SELECT `node_id` FROM `' . $this->dbprefix('analysis_network_node_history') . '`'
				. '    ' . (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. ' GROUP BY `node_id` ORDER BY NULL) AS tnh ON tnh.`node_id`=nl.`node_id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. ' GROUP BY nn.`id` ORDER BY NULL';
		$data = $this->query($sql);
		return $data;
	}

	public function findHrefNode($href_id, $query = NULL) {
		$where = $inner = $nest = array();

		$where[] = 'nn.`href_id`= ' . intval($href_id);
		$nest[] = '`href_id`= ' . intval($href_id);

		if (isset($query['mode']) && isset($query['affected']) && ($mode = trim($query['mode'])) && ($affected = intval($query['affected']))) {
			if ($mode == 'total') {
				$nest[] = '`affected_total` >= ' . $affected;
			} elseif ($mode == 'near') {
				$nest[] = '`affected_count` >= ' . $affected;
			}
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT nn.`id`,nn.`href_id`,nn.`follower_id`,f.`openid`, f.`nickname`,f.`avatarUrl`, '
				. 'nh.`affected_count`,nh.`affected_total`,nh.`view_count`,nh.`forward_count`, nh.`type`, nh.`time` '
				. ' FROM `' . $this->dbprefix('analysis_network_href_node') . '` nn'
				. ' INNER JOIN `mini_programs_follower` f ON f.`id` = nn.`follower_id`'
				. ' INNER JOIN ('
				. '     SELECT MAX(`id`) AS `id`, `node_id` FROM `' . $this->dbprefix('analysis_network_href_node_history') . '`'
				. '     GROUP BY `node_id` ORDER BY NULL) AS mnh ON mnh.`node_id`=nn.`id`'
				. ' INNER JOIN `' . $this->dbprefix('analysis_network_href_node_history') . '` nh ON nh.`id`=mnh.`id`'
				. ' INNER JOIN `' . $this->dbprefix('analysis_network_href_node_level') . '` nl ON nl.`parent_id`=nn.`id`'
				. ' INNER JOIN ('
				. '    SELECT `node_id` FROM `' . $this->dbprefix('analysis_network_href_node_history') . '`'
				. '    ' . (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. ' GROUP BY `node_id` ORDER BY NULL) AS tnh ON tnh.`node_id`=nl.`node_id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. ' GROUP BY nn.`id` ORDER BY NULL';
		$data = $this->query($sql);
		return $data;
	}

	public function findRelation($app_id, $query) {
		$where = $nest = array();

		$where[] = 'nr.`app_id`= ' . intval($app_id);
		$nest[] = '`app_id`= ' . intval($app_id);

		if (isset($query['mode']) && isset($query['affected']) && ($mode = trim($query['mode'])) && ($affected = intval($query['affected']))) {
			if ($mode == 'total') {
				$nest[] = '`affected_total` >= ' . $affected;
			} elseif ($mode == 'near') {
				$nest[] = '`affected_count` >= ' . $affected;
			}
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT nr.`app_id`,nr.`node_id`,nr.`parent_id`'
				. ' FROM `' . $this->dbprefix('analysis_network_node_relation') . '` nr'
				. ' INNER JOIN `' . $this->dbprefix('analysis_network_node_level') . '` nl ON nl.`parent_id` = nr.`node_id`'
				. ' INNER JOIN ('
				. '     SELECT `node_id` FROM `' . $this->dbprefix('analysis_network_node_history') . '`'
				. (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. '     GROUP BY `node_id` ORDER BY NULL) AS nh  ON nh.`node_id`=nl.`node_id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. ' GROUP BY nr.`node_id`,nr.`parent_id`'
				. ' ORDER BY NULL';

		$data = $this->query($sql);
		return $data;
	}

	public function findHrefRelation($href_id, $query) {
		$where = $nest = array();

		$where[] = 'nr.`href_id`= ' . intval($href_id);
		$nest[] = '`href_id`= ' . intval($href_id);

		if (isset($query['mode']) && isset($query['affected']) && ($mode = trim($query['mode'])) && ($affected = intval($query['affected']))) {
			if ($mode == 'total') {
				$nest[] = '`affected_total` >= ' . $affected;
			} elseif ($mode == 'near') {
				$nest[] = '`affected_count` >= ' . $affected;
			}
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT nr.`href_id`,nr.`node_id`,nr.`parent_id`'
				. ' FROM `' . $this->dbprefix('analysis_network_href_node_relation') . '` nr'
				. ' INNER JOIN `' . $this->dbprefix('analysis_network_href_node_level') . '` nl ON nl.`parent_id` = nr.`node_id`'
				. ' INNER JOIN ('
				. '     SELECT `node_id` FROM `' . $this->dbprefix('analysis_network_href_node_history') . '`'
				. (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. '     GROUP BY `node_id` ORDER BY NULL) AS nh  ON nh.`node_id`=nl.`node_id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. ' GROUP BY nr.`node_id`,nr.`parent_id`'
				. ' ORDER BY NULL';

		$data = $this->query($sql);
		return $data;
	}

	public function findRank($app_id, $query = NULL, $offset = 0, $limit = 10) {
		$where = $nest = array();
		$group_by = NULL;
		$ifOrderByNear = FALSE;

		if (isset($query['orderbynear']) && intval($query['orderbynear'])) {
			$ifOrderByNear = TRUE;
		}
		if (isset($query['node_id']) && ($node_id = trim($query['node_id']))) {
			$where[] = 'nl.`parent_id` = ' . intval($node_id);
			$nest[] = '`app_id`= ' . intval($app_id);
		} else {
			$where[] = 'nn.`app_id`= ' . intval($app_id);
			$nest[] = '`app_id`= ' . intval($app_id);
			$group_by = ' GROUP BY nn.`id`';
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT nn.`id`,nn.`app_id`,nn.`follower_id`,'
						. '  f.`platform`,f.`openid`,f.`nickname`,f.`avatarUrl`,'
						. '  nh.`id` AS `nh_id`,nh.`sharetimeline`,nh.`shareappmessage`,nh.`affected_count`,nh.`affected_total`,nh.`view_count`,nh.`forward_count`,nh.`score`,nh.`location`,nh.`level`, nh.`type`, nh.`time`'
						. '  FROM `' . $this->dbprefix('analysis_network_node') . '` nn'
						. '  LEFT JOIN `mini_programs_follower` f ON f.`id` = nn.`follower_id`'
						. '  INNER JOIN `' . $this->dbprefix('analysis_network_node_level') . '` nl ON nl.`node_id` = nn.`id`'
						. '  INNER JOIN ('
						. '     SELECT MAX(`id`) AS `id`,`node_id` FROM `' . $this->dbprefix('analysis_network_node_history') . '`'
						. (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
						. '     GROUP BY `node_id` ORDER BY NULL) AS tnh  ON tnh.`node_id`=nn.`id`'
						. '  INNER JOIN `' . $this->dbprefix('analysis_network_node_history') . '` nh ON nh.`id`=tnh.`id`'
						. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
						. $group_by
						. ($ifOrderByNear ? ' ORDER BY `affected_count` DESC' : ' ORDER BY `affected_total` DESC');

		if ($limit) {
			$sql .= '  LIMIT ' . intval($offset) . ',' . intval($limit);
		}
		
		$data = $this->query($sql);
		return $data;
	}

	public function findHrefRank($href_id, $query = NULL, $offset = 0, $limit = 10) {
		$where = $nest = array();
		$group_by = NULL;
		$ifOrderByNear = FALSE;

		if (isset($query['orderbynear']) && intval($query['orderbynear'])) {
			$ifOrderByNear = TRUE;
		}
		if (isset($query['node_id']) && ($node_id = trim($query['node_id']))) {
			$where[] = 'nl.`parent_id` = ' . intval($node_id);
			$nest[] = '`href_id`= ' . intval($href_id);
		} else {
			$where[] = 'nn.`href_id`= ' . intval($href_id);
			$nest[] = '`href_id`= ' . intval($href_id);
			$group_by = ' GROUP BY nn.`id`';
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT nn.`id`,nn.`href_id`,nn.`follower_id`,'
						. '  f.`platform`,f.`openid`,f.`nickname`,f.`avatarUrl`,'
						. '  nh.`id` AS `nh_id`,nh.`sharetimeline`,nh.`shareappmessage`,nh.`affected_count`,nh.`affected_total`,nh.`view_count`,nh.`forward_count`,nh.`score`,nh.`location`,nh.`level`, nh.`type`, nh.`time`'
						. '  FROM `' . $this->dbprefix('analysis_network_href_node') . '` nn'
						. '  LEFT JOIN `mini_programs_follower` f ON f.`id` = nn.`follower_id`'
						. '  INNER JOIN `' . $this->dbprefix('analysis_network_href_node_level') . '` nl ON nl.`node_id` = nn.`id`'
						. '  INNER JOIN ('
						. '     SELECT MAX(`id`) AS `id`,`node_id` FROM `' . $this->dbprefix('analysis_network_href_node_history') . '`'
						. (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
						. '     GROUP BY `node_id` ORDER BY NULL) AS tnh  ON tnh.`node_id`=nn.`id`'
						. '  INNER JOIN `' . $this->dbprefix('analysis_network_href_node_history') . '` nh ON nh.`id`=tnh.`id`'
						. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
						. $group_by
						. ($ifOrderByNear ? ' ORDER BY `affected_count` DESC' : ' ORDER BY `affected_total` DESC');

		if ($limit) {
			$sql .= '  LIMIT ' . intval($offset) . ',' . intval($limit);
		}
		
		$data = $this->query($sql);
		return $data;
	}

	public function findRankCampaign($campaign_id, $query = NULL, $offset = 0, $limit = 10) {
		$where = $inner = $nest = array();
		$ifOrderByNear = FALSE;

		$where[] = 'ich.`campaign_id`=' . intval($campaign_id);

		if (isset($query['orderbynear']) && intval($query['orderbynear'])) {
			$ifOrderByNear = TRUE;
		}
		if (isset($query['openid']) && ($follower_id = trim($query['openid']))) {
			$where[] = 'rnn.`follower_id` = ' . intval($follower_id);
			$inner[] = ' LEFT JOIN `' . $this->dbprefix('analysis_network_node') . '` rnn ON rnn.`id`=nl.`parent_id`';
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT `follower_id`,`openid`,`nickname`,`avatarUrl`,'
				. ' `nh_id`,`sharetimeline`,`shareappmessage`,SUM(`affected_count`) AS `affected_count`,SUM(`affected_total`) AS `affected_total`,SUM(`view_count`) AS `view_count`,SUM(`forward_count`) AS `forward_count`,'
				. ' `score`,`location`,`level`,`type`, `time`'
				. ' FROM ('
				. '  SELECT nn.`follower_id`,'
				. '     f.`openid`,f.`nickname`,f.`avatarUrl`,'
				. '     nh.`id` AS `nh_id`, nh.`sharetimeline`,nh.`shareappmessage`,nh.`affected_count`,nh.`affected_total`,nh.`view_count`,nh.`forward_count`,nh.`score`,nh.`location`,nh.`level`, nh.`type`, nh.`time`'
				. ' FROM `' . $this->dbprefix('analysis_network_node') . '` nn'
				. ' LEFT JOIN `' . $this->dbprefix('follower') . '` f ON f.`id` = nn.`follower_id`'
				. ' INNER JOIN `' . $this->dbprefix('analysis_network_node_level') . '` nl ON nl.`node_id` = nn.`id`'
				. (empty($inner) ? '' : implode(' ', $inner))
				. ' INNER JOIN ('
				. '     SELECT MAX(`id`) AS `id`,`node_id` FROM `' . $this->dbprefix('analysis_network_node_history') . '`'
				. ' ' . (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. '     GROUP BY `node_id` ORDER BY NULL) AS tnh  ON tnh.`node_id`=nn.`id`'
				. ' INNER JOIN `' . $this->dbprefix('analysis_network_node_history') . '` nh ON nh.`id`=tnh.`id`'
				. ' INNER JOIN `' . $this->dbprefix('analysis_campaign_href') . '` ich ON ich.`app_id`=nn.`app_id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. ' GROUP BY nn.`id`'
				. ' ORDER BY NULL) AS tmp'
				. ' GROUP BY `follower_id`'
				. ($ifOrderByNear ? ' ORDER BY `affected_count` DESC' : ' ORDER BY `affected_total` DESC');

		if ($limit) {
			$sql .= '  LIMIT ' . intval($offset) . ',' . intval($limit);
		}

		$data = $this->query($sql);
		return $data;
	}

	public function sumScore($app_id, $query = NULL) {
		$where = $nest = array();
		$group_by = NULL;

		if (isset($query['node_id']) && ($node_id = trim($query['node_id']))) {
			$where[] = 'nl.`parent_id` = ' . intval($node_id);
			$nest[] = '`app_id`= ' . intval($app_id);
		} else {
			$where[] = 'nn.`app_id`= ' . intval($app_id);
			$nest[] = '`app_id`= ' . intval($app_id);
			$group_by = ' GROUP BY nn.`id` ORDER BY NULL';
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT SUM(`score`) score FROM '
				. ' (SELECT nn.`id`,nh.`score` FROM `' . $this->dbprefix('analysis_network_node') . '` nn'
				. '  INNER JOIN `' . $this->dbprefix('analysis_network_node_level') . '` nl ON nl.`node_id` = nn.`id`'
				. '  INNER JOIN ('
				. '     SELECT MAX(`id`) AS `id`,`node_id` FROM `' . $this->dbprefix('analysis_network_node_history') . '`'
				. (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. '     GROUP BY `node_id` ORDER BY NULL) AS tnh  ON tnh.`node_id`=nn.`id`'
				. '  INNER JOIN `' . $this->dbprefix('analysis_network_node_history') . '` nh ON nh.`id`=tnh.`id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. $group_by . ') AS tmp';

		$data = $this->query($sql);

		return isset($data[0]) ? array_pop($data[0]) : 0;
	}

	public function sumHrefScore($href_id, $query = NULL) {
		$where = $nest = array();
		$group_by = NULL;

		if (isset($query['node_id']) && ($node_id = trim($query['node_id']))) {
			$where[] = 'nl.`parent_id` = ' . intval($node_id);
			$nest[] = '`href_id`= ' . intval($href_id);
		} else {
			$where[] = 'nn.`href_id`= ' . intval($href_id);
			$nest[] = '`href_id`= ' . intval($href_id);
			$group_by = ' GROUP BY nn.`id` ORDER BY NULL';
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT SUM(`score`) score FROM '
				. ' (SELECT nn.`id`,nh.`score` FROM `' . $this->dbprefix('analysis_network_href_node') . '` nn'
				. '  INNER JOIN `' . $this->dbprefix('analysis_network_href_node_level') . '` nl ON nl.`node_id` = nn.`id`'
				. '  INNER JOIN ('
				. '     SELECT MAX(`id`) AS `id`,`node_id` FROM `' . $this->dbprefix('analysis_network_href_node_history') . '`'
				. (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. '     GROUP BY `node_id` ORDER BY NULL) AS tnh  ON tnh.`node_id`=nn.`id`'
				. '  INNER JOIN `' . $this->dbprefix('analysis_network_href_node_history') . '` nh ON nh.`id`=tnh.`id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. $group_by . ') AS tmp';

		$data = $this->query($sql);

		return isset($data[0]) ? array_pop($data[0]) : 0;
	}

	public function countRank($app_id, $query = NULL) {
		$where = $nest = array();
		$group_by = NULL;

		if (isset($query['node_id']) && ($node_id = trim($query['node_id']))) {
			$where[] = 'nl.`parent_id` = ' . intval($node_id);
			$nest[] = '`app_id`= ' . intval($app_id);
		} else {
			$where[] = 'nn.`app_id`= ' . intval($app_id);
			$nest[] = '`app_id`= ' . intval($app_id);
			$group_by = ' GROUP BY nn.`id` ORDER BY NULL';
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT COUNT(1) FROM '
				. ' (SELECT nn.`id` FROM `' . $this->dbprefix('analysis_network_node') . '` nn'
				. '  INNER JOIN `' . $this->dbprefix('analysis_network_node_level') . '` nl ON nl.`node_id` = nn.`id`'
				. '  INNER JOIN ('
				. '     SELECT MAX(`id`) AS `id`,`node_id` FROM `' . $this->dbprefix('analysis_network_node_history') . '`'
				. (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. '     GROUP BY `node_id` ORDER BY NULL) AS tnh  ON tnh.`node_id`=nn.`id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. $group_by . ') AS tmp';

		$data = $this->query($sql);
		return isset($data[0]) ? array_pop($data[0]) : 0;
	}

	public function countHrefRank($href_id, $query = NULL) {
		$where = $nest = array();
		$group_by = NULL;

		if (isset($query['node_id']) && ($node_id = trim($query['node_id']))) {
			$where[] = 'nl.`parent_id` = ' . intval($node_id);
			$nest[] = '`href_id`= ' . intval($href_id);
		} else {
			$where[] = 'nn.`href_id`= ' . intval($href_id);
			$nest[] = '`href_id`= ' . intval($href_id);
			$group_by = ' GROUP BY nn.`id` ORDER BY NULL';
		}
		if (isset($query['from']) && $from = intval($query['from'])) {
			$nest[] = '`time` >= ' . $from;
		}
		if (isset($query['to']) && $to = intval($query['to'])) {
			$nest[] = '`time` <= ' . $to;
		}

		$sql = 'SELECT COUNT(1) FROM '
				. ' (SELECT nn.`id` FROM `' . $this->dbprefix('analysis_network_href_node') . '` nn'
				. '  INNER JOIN `' . $this->dbprefix('analysis_network_href_node_level') . '` nl ON nl.`node_id` = nn.`id`'
				. '  INNER JOIN ('
				. '     SELECT MAX(`id`) AS `id`,`node_id` FROM `' . $this->dbprefix('analysis_network_href_node_history') . '`'
				. (empty($nest) ? '' : (' WHERE ' . implode(' AND ', $nest)))
				. '     GROUP BY `node_id` ORDER BY NULL) AS tnh  ON tnh.`node_id`=nn.`id`'
				. (empty($where) ? '' : (' WHERE ' . implode(' AND ', $where)))
				. $group_by . ') AS tmp';

		$data = $this->query($sql);
		return isset($data[0]) ? array_pop($data[0]) : 0;
	}

	public function build_node($href, $visitor, $type = self::TYPE_READ) {
		$node = array(
			'app_id' => $href['id'],
			'follower_id' => $visitor['id'],
			'type' => $type,
			'sharetimeline' => 0,
			'shareappmessage' => 0,
			'score' => 0,
			'affected_count' => 0,
			'affected_total' => 0,
			'view_count' => 0,
			'forward_count' => 0,
			'ip_addr' => $href['ip_addr'],
			'location' => $href['location']
		);

		$node['id'] = $this->name('w_analysis_network_node')->insertGetId($node);
		if( !$node['id'] ) {
			return 0;
		}

		$node['level_path'] = $node['id'] . ';';
		$node['level'] = 0;

		$this->name('w_analysis_network_node')->where('id' ,intval($node['id']) )->update( array('level' => intval($node['level']),
			'level_path' => $node['level_path']));

		return $node;
	}

	public function build_href_node($href, $visitor, $type = self::TYPE_READ) {
		$node = array(
			'href_id' => $href['href_id'],
			'follower_id' => $visitor['id'],
			'type' => $type,
			'sharetimeline' => 0,
			'shareappmessage' => 0,
			'score' => 0,
			'affected_count' => 0,
			'affected_total' => 0,
			'view_count' => 0,
			'forward_count' => 0,
			'ip_addr' => $href['ip_addr'],
			'location' => $href['location']
		);

		$node['id'] = $this->name('w_analysis_network_href_node')->insertGetId($node);
		if( !$node['id'] ) {
			return 0;
		}

		$node['level_path'] = $node['id'] . ';';
		$node['level'] = 0;

		$this->name('w_analysis_network_href_node')->where('id' ,intval($node['id']) )->update( array('level' => intval($node['level']),
			'level_path' => $node['level_path']));

		return $node;
	}

	public function update_node($node, $update, $time) {
		if (count($update)) {
			$this->query('UPDATE `' . $this->dbprefix('analysis_network_node') . '`'
					. ' SET ' . implode(', ', $update) . ' WHERE `id`=' . intval($node['id']));

			$sql = 'INSERT INTO `' . $this->dbprefix('analysis_network_node_history') . '` (`app_id`,`node_id`,`time`,`type`,`sharetimeline`,`shareappmessage`,`score`,`affected_count`,`affected_total`,`view_count`,`forward_count`,`ip_addr`,`location`,`level`,`level_path`)'
					. ' SELECT `app_id`,`id`,' . intval($time) . ',`type`,`sharetimeline`,`shareappmessage`,`score`,`affected_count`,`affected_total`,`view_count`,`forward_count`,`ip_addr`,`location`,`level`,`level_path`'
					. ' FROM `' . $this->dbprefix('analysis_network_node') . '` WHERE `id`=' . intval($node['id']);
			$this->query($sql);
		}
	}

	public function update_href_node($node, $update, $time) {
		if (count($update)) {
			$this->query('UPDATE `' . $this->dbprefix('analysis_network_href_node') . '`'
					. ' SET ' . implode(', ', $update) . ' WHERE `id`=' . intval($node['id']));

			$sql = 'INSERT INTO `' . $this->dbprefix('analysis_network_href_node_history') . '` (`href_id`,`node_id`,`time`,`type`,`sharetimeline`,`shareappmessage`,`score`,`affected_count`,`affected_total`,`view_count`,`forward_count`,`ip_addr`,`location`,`level`,`level_path`)'
					. ' SELECT `href_id`,`id`,' . intval($time) . ',`type`,`sharetimeline`,`shareappmessage`,`score`,`affected_count`,`affected_total`,`view_count`,`forward_count`,`ip_addr`,`location`,`level`,`level_path`'
					. ' FROM `' . $this->dbprefix('analysis_network_href_node') . '` WHERE `id`=' . intval($node['id']);
			$this->query($sql);
		}
	}

	public function build_relation($node, $parent_node, $level = array()) {
		$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_node_relation') . '`'
				. ' WHERE `node_id` = ' . intval($node['id'])
				. ' AND `parent_id` = ' . intval($parent_node['id']);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			return FALSE;
		}

		$this->name('w_analysis_network_node_relation')->insert( array(
			'node_id' => $node['id'],
			'parent_id' => $parent_node['id'],
			'app_id' => $node['app_id']
		));
		
		foreach ($level as $one) {
			$this->query('REPLACE INTO `' . $this->dbprefix('analysis_network_node_level') . '`(`app_id`,`node_id`,`parent_id`,`nearest_parent_id`)'
					. ' VALUES(' . intval($node['app_id']) . ',' . intval($node['id']) . ',' . intval($one['id']) . ',' . intval($one['nearest_parent_id']) . ')');
		}

		$this->query('REPLACE INTO `' . $this->dbprefix('analysis_network_node_level') . '`(`app_id`,`node_id`,`parent_id`,`nearest_parent_id`)'
				. ' VALUES(' . intval($node['app_id']) . ',' . intval($node['id']) . ',' . intval($node['id']) . ',' . intval($parent_node['id']) . ')');

		return TRUE;
	}

	public function build_href_relation($node, $parent_node, $level = array()) {
		$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_href_node_relation') . '`'
				. ' WHERE `node_id` = ' . intval($node['id'])
				. ' AND `parent_id` = ' . intval($parent_node['id']);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			return FALSE;
		}

		$this->name('w_analysis_network_href_node_relation')->insert( array(
			'node_id' => $node['id'],
			'parent_id' => $parent_node['id'],
			'href_id' => $node['href_id']
		));
		
		foreach ($level as $one) {
			$this->query('REPLACE INTO `' . $this->dbprefix('analysis_network_href_node_level') . '`(`href_id`,`node_id`,`parent_id`,`nearest_parent_id`)'
					. ' VALUES(' . intval($node['href_id']) . ',' . intval($node['id']) . ',' . intval($one['id']) . ',' . intval($one['nearest_parent_id']) . ')');
		}

		$this->query('REPLACE INTO `' . $this->dbprefix('analysis_network_href_node_level') . '`(`href_id`,`node_id`,`parent_id`,`nearest_parent_id`)'
				. ' VALUES(' . intval($node['href_id']) . ',' . intval($node['id']) . ',' . intval($node['id']) . ',' . intval($parent_node['id']) . ')');

		return TRUE;
	}

	public function update_relation($node, $level, $time) {
		$this->protection_stack[$node['id']] = 1;

		foreach ($level as $one) {
			if ($one['id'] == $node['id']) {
				continue;
			}
			if ($one['id'] != $node['nearest_parent_id']) {
				continue;
			}

			$update = array();
			$update[] = '`type`=' . intval(self::TYPE_FORWARD);
			$update[] = '`affected_total`=`affected_total`+1';

			if ($node['level'] >= $one['level'] && (strpos($node['level_path'], $one['id'] . ';') === FALSE)) {
				$one['level_path'] = $one['id'] . ';' . $node['level_path'];
				$one['level'] = count(array_filter(explode(';', $one['level_path']))) - 1;

				$update[] = '`level_path`=' . $this->getConnection()->quote($one['level_path']);
				$update[] = '`level`=' . intval($one['level']);
			}

			$this->update_node($one, $update, $time);

			if (!isset($this->protection_stack[$one['id']])) {
				// 若未处理过，则开始处理
				$this->update_relation($one, $level, $time);
			}
		}
	}

	public function update_href_relation($node, $level, $time) {
		$this->protection_stack[$node['id']] = 1;

		foreach ($level as $one) {
			if ($one['id'] == $node['id']) {
				continue;
			}
			if ($one['id'] != $node['nearest_parent_id']) {
				continue;
			}

			$update = array();
			$update[] = '`type`=' . intval(self::TYPE_FORWARD);
			$update[] = '`affected_total`=`affected_total`+1';

			if ($node['level'] >= $one['level'] && (strpos($node['level_path'], $one['id'] . ';') === FALSE)) {
				$one['level_path'] = $one['id'] . ';' . $node['level_path'];
				$one['level'] = count(array_filter(explode(';', $one['level_path']))) - 1;

				$update[] = '`level_path`=' . $this->getConnection()->quote($one['level_path']);
				$update[] = '`level`=' . intval($one['level']);
			}

			$this->update_href_node($one, $update, $time);

			if (!isset($this->protection_stack[$one['id']])) {
				// 若未处理过，则开始处理
				$this->update_href_relation($one, $level, $time);
			}
		}
	}

	public function forward($href, $visitor, $source, $time = NULL) {
		$parent_node = array();
		$parent_node['id'] = 0;

		if (!$time) {
			$time = time();
		}
		if ($source) {
			AGAIN_SOURCE:
			$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_node') . '`'
					. ' WHERE `app_id` = ' . intval($href['id'])
					. ' AND `follower_id` = ' . intval($source['id']);
			$data = $this->query($sql);
			$update_href = array();
			$update = array();

			if ( isset($data[0]) ) {
				$node = $data[0];

				$update_href[] = '`forward_count`=`forward_count`+1';
				if ($node['type'] == self::TYPE_READ) {
					// 若之前如果只是阅读
					$update_href[] = '`forward_visitor`=`forward_visitor`+1';
				}
			} else {
				$node = $this->build_node($href, $source, self::TYPE_READ);
				if( $node === 0 ) {
					goto AGAIN_SOURCE;
				}
				$this->build_relation($node, $parent_node);

				$update_href[] = '`forward_count`=`forward_count`+1';
				$update_href[] = '`forward_visitor`=`forward_visitor`+1';
				$update_href[] = '`affected_count`=`affected_count`+1';
			}
			if ($visitor['id'] == $source['id']) {
				// 若用户当前分享的是自己分享出去的链接
				// 只更新被分享数, 不更新关系数据
				$update[] = '`type`=' . intval(self::TYPE_FORWARD);
				$update[] = '`forward_count`=`forward_count`+1';
				$update[] = '`ip_addr`=' . $this->getConnection()->quote($href['ip_addr']);
				$update[] = '`location`=' . $this->getConnection()->quote($href['location']);

				if ($href['score'] > 0) {
					// 累加评分，对象仅为当前节点
					$update[] = '`score`=`score`+' . intval($href['score']);
				}
				if (strtolower($href['method']) == 'menusharetimeline') {
					$update[] = '`sharetimeline` = `sharetimeline` + 1';
				} elseif (strtolower($href['method']) == 'menushareappmessage') {
					$update[] = '`shareappmessage` = `shareappmessage` + 1';
				}

				$this->update_node($node, $update, $time);
				$this->update_href($href, $update_href);

				return $node['id'];
			}
			// 设置当前父节点
			$parent_node = $node;
		}

		$update_href = array();
		$update = array();
		$level = array();
		$update_href[] = '`forward_count`=`forward_count`+1';

		AGAIN:
		$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_node') . '`'
				. ' WHERE `app_id` = ' . intval($href['id'])
				. ' AND `follower_id` = ' . intval($visitor['id']);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			$node = $data[0];

			if ($node['type'] == self::TYPE_READ) {
				// 若节点之前如果只是阅读, 修改为转发节点
				$update[] = '`type`=' . intval(self::TYPE_FORWARD);
				$update_href[] = '`forward_visitor`=`forward_visitor`+1';
			}
		} else {
			$update_href[] = '`forward_visitor`=`forward_visitor`+1';
			$node = $this->build_node($href, $visitor, self::TYPE_FORWARD);
			if( $node === 0 ) {
				goto AGAIN;
			}
		}

		$update[] = '`ip_addr`=' . $this->getConnection()->quote($href['ip_addr']);
		$update[] = '`location`=' . $this->getConnection()->quote($href['location']);

		if ($href['score'] > 0) {
			// 累加评分，对象仅为当前节点
			$update[] = '`score`=`score`+' . intval($href['score']);
		}
		if (strtolower($href['method']) == 'menusharetimeline') {
			$update[] = '`sharetimeline` = `sharetimeline` + 1';
		} elseif (strtolower($href['method']) == 'menushareappmessage') {
			$update[] = '`shareappmessage` = `shareappmessage` + 1';
		}
		// 更新当前节点
		$this->update_node($node, $update, $time);

		if ($parent_node['id'] > 0) {
			// 取得上一级节点的层级关系
			$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_node_level') . '` nl'
					. ' INNER JOIN `' . $this->dbprefix('analysis_network_node') . '` nn ON nn.`id`=nl.`parent_id`'
					. ' WHERE nl.`node_id` = ' . intval($parent_node['id']);
			$level = $this->query($sql);
		}

		$parent_update = array();
		$parent_update[] = '`type`=' . intval(self::TYPE_FORWARD);
		$parent_update[] = '`forward_count`=`forward_count`+1';

		if ($this->build_relation($node, $parent_node, $level)) {
			$parent_update[] = '`affected_count`=`affected_count`+1';
			$update_href[] = '`affected_count`=`affected_count`+1';
			$node['nearest_parent_id'] = $parent_node['id'];
			$this->protection_stack = array();

			// 更新上一级节点
			$this->update_node($parent_node, $parent_update, $time);
			// 更新关系数据
			$this->update_relation($node, $level, $time);
		} elseif ($parent_node['id'] > 0) {
			$this->update_node($parent_node, $parent_update, $time);
		}

		$this->update_href($href, $update_href);

		return $node['id'];
	}

	public function forward_href($href, $visitor, $source, $time = NULL) {
		$parent_node = array();
		$parent_node['id'] = 0;

		if (!$time) {
			$time = time();
		}
		if ($source) {
			AGAIN_SOURCE:
			$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_href_node') . '`'
					. ' WHERE `href_id` = ' . intval($href['href_id'])
					. ' AND `follower_id` = ' . intval($source['id']);
			$data = $this->query($sql);
			$update_href = array();
			$update = array();

			if ( isset($data[0]) ) {
				$node = $data[0];

				$update_href[] = '`forward_count`=`forward_count`+1';
				if ($node['type'] == self::TYPE_READ) {
					// 若之前如果只是阅读
					$update_href[] = '`forward_visitor`=`forward_visitor`+1';
				}
			} else {
				$node = $this->build_href_node($href, $source, self::TYPE_READ);
				if( $node === 0 ) {
					goto AGAIN_SOURCE;
				}
				$this->build_href_relation($node, $parent_node);

				$update_href[] = '`forward_count`=`forward_count`+1';
				$update_href[] = '`forward_visitor`=`forward_visitor`+1';
				$update_href[] = '`affected_count`=`affected_count`+1';
			}
			if ($visitor['id'] == $source['id']) {
				// 若用户当前分享的是自己分享出去的链接
				// 只更新被分享数, 不更新关系数据
				$update[] = '`type`=' . intval(self::TYPE_FORWARD);
				$update[] = '`forward_count`=`forward_count`+1';
				$update[] = '`ip_addr`=' . $this->getConnection()->quote($href['ip_addr']);
				$update[] = '`location`=' . $this->getConnection()->quote($href['location']);

				if ($href['score'] > 0) {
					// 累加评分，对象仅为当前节点
					$update[] = '`score`=`score`+' . intval($href['score']);
				}
				if (strtolower($href['method']) == 'menusharetimeline') {
					$update[] = '`sharetimeline` = `sharetimeline` + 1';
				} elseif (strtolower($href['method']) == 'menushareappmessage') {
					$update[] = '`shareappmessage` = `shareappmessage` + 1';
				}

				$this->update_href_node($node, $update, $time);
				$this->update_href_href($href, $update_href);

				return $node['id'];
			}
			// 设置当前父节点
			$parent_node = $node;
		}

		$update_href = array();
		$update = array();
		$level = array();
		$update_href[] = '`forward_count`=`forward_count`+1';

		AGAIN:
		$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_href_node') . '`'
				. ' WHERE `href_id` = ' . intval($href['href_id'])
				. ' AND `follower_id` = ' . intval($visitor['id']);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			$node = $data[0];

			if ($node['type'] == self::TYPE_READ) {
				// 若节点之前如果只是阅读, 修改为转发节点
				$update[] = '`type`=' . intval(self::TYPE_FORWARD);
				$update_href[] = '`forward_visitor`=`forward_visitor`+1';
			}
		} else {
			$node = $this->build_href_node($href, $visitor, self::TYPE_FORWARD);
			if( $node === 0 ) {
				goto AGAIN;
			}
			$update_href[] = '`forward_visitor`=`forward_visitor`+1';
		}

		$update[] = '`ip_addr`=' . $this->getConnection()->quote($href['ip_addr']);
		$update[] = '`location`=' . $this->getConnection()->quote($href['location']);

		if ($href['score'] > 0) {
			// 累加评分，对象仅为当前节点
			$update[] = '`score`=`score`+' . intval($href['score']);
		}
		if (strtolower($href['method']) == 'menusharetimeline') {
			$update[] = '`sharetimeline` = `sharetimeline` + 1';
		} elseif (strtolower($href['method']) == 'menushareappmessage') {
			$update[] = '`shareappmessage` = `shareappmessage` + 1';
		}
		// 更新当前节点
		$this->update_href_node($node, $update, $time);

		if ($parent_node['id'] > 0) {
			// 取得上一级节点的层级关系
			$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_href_node_level') . '` nl'
					. ' INNER JOIN `' . $this->dbprefix('analysis_network_href_node') . '` nn ON nn.`id`=nl.`parent_id`'
					. ' WHERE nl.`node_id` = ' . intval($parent_node['id']);
			$level = $this->query($sql);
		}

		$parent_update = array();
		$parent_update[] = '`type`=' . intval(self::TYPE_FORWARD);
		$parent_update[] = '`forward_count`=`forward_count`+1';

		if ($this->build_href_relation($node, $parent_node, $level)) {
			$parent_update[] = '`affected_count`=`affected_count`+1';
			$update_href[] = '`affected_count`=`affected_count`+1';
			$node['nearest_parent_id'] = $parent_node['id'];
			$this->protection_stack = array();

			// 更新上一级节点
			$this->update_href_node($parent_node, $parent_update, $time);
			// 更新关系数据
			$this->update_href_relation($node, $level, $time);
		} elseif ($parent_node['id'] > 0) {
			$this->update_href_node($parent_node, $parent_update, $time);
		}

		$this->update_href_href($href, $update_href);

		return $node['id'];
	}

	// 小程序
	public function page($href, $insert) {
		$token_href = $insert['token'] = md5($insert['uniq'] . $href['id']);
		$time = $insert['time'] / 1000;
		$table = $this->get_ja_table($time);
		$onlyPV = FALSE;
		$hour = date('Y-m-d H:00:00', $time);
		$token_hour = md5($href['id'] . $hour);

		$sql = 'SELECT `id` FROM `' . $this->dbprefix('app_ping_hour_summary') . '`'
				. ' WHERE `token`=:token_hour limit 1';
		$data = $this->query($sql, [ 'token_hour' => $token_hour]);

		if ( isset( $data[0] ) ) {
			$id = array_pop( $data[0] );
			$from = strtotime($hour);
			$to = strtotime(date('Y-m-d H:00:00', strtotime($hour . ' +1 hour')));
			$sql = 'SELECT `id` FROM `' . $table . '`'
					. ' WHERE `token`= :token_href' 
					. ' AND `time`>' . intval($from * 1000)
					. ' AND `time`<' . intval($to * 1000)
					. ' LIMIT 1';
			
			$data = $this->query($sql, [ 'token_href' => $token_href ]);
			
			if ( isset( $data[0] ) ) {
				$onlyPV = TRUE;
				$this->query('UPDATE `' . $this->dbprefix('app_ping_hour_summary') . '`'
						. ' SET `pv`=`pv`+1 WHERE `id`=' . intval($id));
			} else {
				$this->query('UPDATE `' . $this->dbprefix('app_ping_hour_summary') . '`'
						. ' SET `pv`=`pv`+1, `pu`=`pu`+1 WHERE `id`=' . intval($id));
			}
		} else {
			$this->name('w_app_ping_hour_summary')->insert( array(
				'token' => $token_hour,
				'app_id' => $href['id'],
				'time' => $hour,
				'pv' => 1,
				'pu' => 1
			));
		}
		
		$today = date('Y-m-d', $time);
		$token_today = md5($href['id'] . $today);
		$sql = 'SELECT `id` FROM `' . $this->dbprefix('app_ping_day_summary') . '`'
				. ' WHERE `token`=:token_today limit 1';
		$data = $this->query($sql, [ 'token_today' => $token_today]);

		if ( isset( $data[0] ) ) {
			$id = array_pop( $data[0] );

			if ($onlyPV) {
				$this->query('UPDATE `' . $this->dbprefix('app_ping_day_summary') . '`'
						. ' SET `pv`=`pv`+1 WHERE `id`=' . intval($id));

				$this->name($table)->insert( $insert );
				return;
			}

			$from = strtotime($today);
			$to = strtotime(date('Y-m-d', strtotime($today . ' +1 days')));
			$sql = 'SELECT `id` FROM `' . $table . '`'
					. ' WHERE `token`=:token_href'
					. ' AND `time`>' . intval($from * 1000)
					. ' AND `time`<' . intval($to * 1000)
					. ' LIMIT 1';

			$data = $this->query($sql, [ 'token_href' => $token_href]);
			if ( isset( $data[0] ) ) {
				$this->query('UPDATE `' . $this->dbprefix('app_ping_day_summary') . '`'
						. ' SET `pv`=`pv`+1 WHERE `id`=' . intval($id));
			} else {
				$this->query('UPDATE `' . $this->dbprefix('app_ping_day_summary') . '`'
						. ' SET `pv`=`pv`+1, `pu`=`pu`+1 WHERE `id`=' . intval($id));
			}
		} else {
			$this->name('w_app_ping_day_summary')->insert( array(
				'token' => $token_today,
				'app_id' => $href['id'],
				'time' => $today,
				'pv' => 1,
				'pu' => 1
			));
		}

		$this->name($table)->insert( $insert );
	}

	// 小程序页面
	public function page_href($href, $insert) {
		$token_href = $insert['token'] = md5($insert['uniq'] . $href['href_id']);
		$time = $insert['time'] / 1000;
		$table = $this->get_ja_href_table($time);
		$onlyPV = FALSE;
		$hour = date('Y-m-d H:00:00', $time);
		$token_href_hour = md5($href['href_id'] . $hour);


		$sql = 'SELECT `id` FROM `' . $this->dbprefix('app_href_ping_hour_summary') . '`'
				. ' WHERE `token`=:token_href_hour limit 1';
		$data = $this->query($sql, [ 'token_href_hour' => $token_href_hour]);

		if ( isset( $data[0] ) ) {
			$id = array_pop( $data[0] );
			$from = strtotime($hour);
			$to = strtotime(date('Y-m-d H:00:00', strtotime($hour . ' +1 hour')));
			$sql = 'SELECT `id` FROM `' . $table . '`'
					. ' WHERE `token`= :token_href' 
					. ' AND `time`>' . intval($from * 1000)
					. ' AND `time`<' . intval($to * 1000)
					. ' LIMIT 1';
			
			$data = $this->query($sql, [ 'token_href' => $token_href ]);
			if ( isset( $data[0] ) ) {
				$onlyPV = TRUE;
				$this->query('UPDATE `' . $this->dbprefix('app_href_ping_hour_summary') . '`'
						. ' SET `pv`=`pv`+1 WHERE `id`=' . intval($id));
			} else {
				$this->query('UPDATE `' . $this->dbprefix('app_href_ping_hour_summary') . '`'
						. ' SET `pv`=`pv`+1, `pu`=`pu`+1 WHERE `id`=' . intval($id));
			}
		} else {
			$this->name('w_app_href_ping_hour_summary')->insert( array(
				'token' => $token_href_hour,
				'href_id' => $href['href_id'],
				'time' => $hour,
				'pv' => 1,
				'pu' => 1
			));
		}
		//-------------------------------------小程序页面--------------------------------------------

		$today = date('Y-m-d', $time);
		$token_href_today = md5($href['href_id'] . $today);

		//-------------------------------------小程序页面--------------------------------------------
		$sql = 'SELECT `id` FROM `' . $this->dbprefix('app_href_ping_day_summary') . '`'
				. ' WHERE `token`=:token_href_today limit 1';
		$data = $this->query($sql, [ 'token_href_today' => $token_href_today]);

		if ( isset( $data[0] ) ) {
			$id = array_pop( $data[0] );

			if ($onlyPV) {
				$this->query('UPDATE `' . $this->dbprefix('app_href_ping_day_summary') . '`'
						. ' SET `pv`=`pv`+1 WHERE `id`=' . intval($id));
				$this->name($table)->insert( $insert );
				return;
			}

			$from = strtotime($today);
			$to = strtotime(date('Y-m-d', strtotime($today . ' +1 days')));
			$sql = 'SELECT `id` FROM `' . $table . '`'
					. ' WHERE `token`=:token_href'
					. ' AND `time`>' . intval($from * 1000)
					. ' AND `time`<' . intval($to * 1000)
					. ' LIMIT 1';

			$data = $this->query($sql, [ 'token_href' => $token_href]);
			if ( isset( $data[0] ) ) {
				$this->query('UPDATE `' . $this->dbprefix('app_href_ping_day_summary') . '`'
						. ' SET `pv`=`pv`+1 WHERE `id`=' . intval($id));
			} else {
				$this->query('UPDATE `' . $this->dbprefix('app_href_ping_day_summary') . '`'
						. ' SET `pv`=`pv`+1, `pu`=`pu`+1 WHERE `id`=' . intval($id));
			}
		} else {
			$this->name('w_app_href_ping_day_summary')->insert( array(
				'token' => $token_href_today,
				'href_id' => $href['href_id'],
				'time' => $today,
				'pv' => 1,
				'pu' => 1
			));
		}
		$this->name($table)->insert( $insert );
	}

	public function share($href, $insert) {
		$token_href = $insert['token'] = md5($insert['uniq'] . $href['id'] . $insert['share_result']);

		if (!(intval($insert['share_result']) > 0)) {
			$this->name('w_js_share')->insert($insert);
			return;
		}

		$time = $insert['time'] / 1000;
		$hour = date('Y-m-d H:00:00', $time);
		$token_hour = md5($href['id'] . $hour);
		$onlyPV = FALSE;

		$sql = 'SELECT `id` FROM `' . $this->dbprefix('app_share_hour_summary') . '`'
				. ' WHERE `token` = ' . $this->getConnection()->quote($token_hour);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			$id = array_pop($data[0]);

			$from = strtotime($hour);
			$to = strtotime(date('Y-m-d H:00:00', strtotime($hour . ' +1 hour')));
			$sql = 'SELECT `id` FROM `' . $this->dbprefix('js_share') . '`'
					. ' WHERE `token`=' . $this->getConnection()->quote($token_href)
					. ' AND `time`>' . intval($from * 1000)
					. ' AND `time`<' . intval($to * 1000)
					. ' LIMIT 1';
			$data = $this->query($sql);

			if ( isset($data[0]) ) {
				$onlyPV = TRUE;
				$this->query('UPDATE `' . $this->dbprefix('app_share_hour_summary') . '`'
						. ' SET `sc`=`sc`+1 WHERE `id`=' . intval($id));
			} else {
				$this->query('UPDATE `' . $this->dbprefix('app_share_hour_summary') . '`'
						. ' SET `sc`=`sc`+1, `su`=`su`+1  WHERE `id`=' . intval($id));
			}
		} else {
			$this->name('w_app_share_hour_summary')->insert( array(
				'token' => $token_hour,
				'app_id' => $href['id'],
				'time' => $hour,
				'sc' => 1,
				'su' => 1
			));
		}

		$today = date('Y-m-d', $time);
		$token_today = md5($href['id'] . $today);
		$sql = 'SELECT `id` FROM `' . $this->dbprefix('app_share_day_summary') . '`'
				. ' WHERE `token` = ' . $this->getConnection()->quote($token_today);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			$id = array_pop($data[0]);

			if ($onlyPV) {
				$this->query('UPDATE `' . $this->dbprefix('app_share_day_summary') . '`'
						. ' SET `sc`=`sc`+1 WHERE `id`=' . intval($id));
				$this->name('w_js_share')->insert($insert);
				return;
			}

			$from = strtotime($today);
			$to = strtotime(date('Y-m-d', strtotime($today . ' +1 days')));
			$sql = 'SELECT `id` FROM `' . $this->dbprefix('js_share') . '`'
					. ' WHERE `token`=' . $this->getConnection()->quote($token_href)
					. ' AND `time`>' . intval($from * 1000)
					. ' AND `time`<' . intval($to * 1000)
					. ' LIMIT 1';
			$data = $this->query($sql);

			if ( isset($data[0]) ) {
				$this->query('UPDATE `' . $this->dbprefix('app_share_day_summary') . '`'
						. ' SET `sc`=`sc`+1 WHERE `id`=' . intval($id));
			} else {
				$this->query('UPDATE `' . $this->dbprefix('app_share_day_summary') . '`'
						. ' SET `sc`=`sc`+1, `su`=`su`+1  WHERE `id`=' . intval($id));
			}
		} else {
			$this->name('w_app_share_day_summary')->insert(array(
				'token' => $token_today,
				'app_id' => $href['id'],
				'time' => $today,
				'sc' => 1,
				'su' => 1
			));
		}

		$this->name('w_js_share')->insert($insert);
	}

	public function share_href($href, $insert) {
		$token_href = $insert['token'] = md5($insert['uniq'] . $href['href_id'] . $insert['share_result']);

		$time = $insert['time'] / 1000;
		$hour = date('Y-m-d H:00:00', $time);
		$token_hour = md5($href['id'] . $hour);
		$onlyPV = FALSE;

		$sql = 'SELECT `id` FROM `' . $this->dbprefix('app_href_share_hour_summary') . '`'
				. ' WHERE `token` = ' . $this->getConnection()->quote($token_hour);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			$id = array_pop($data[0]);

			$from = strtotime($hour);
			$to = strtotime(date('Y-m-d H:00:00', strtotime($hour . ' +1 hour')));
			$sql = 'SELECT `id` FROM `' . $this->dbprefix('js_share_href') . '`'
					. ' WHERE `token`=' . $this->getConnection()->quote($token_href)
					. ' AND `time`>' . intval($from * 1000)
					. ' AND `time`<' . intval($to * 1000)
					. ' LIMIT 1';
			$data = $this->query($sql);

			if ( isset($data[0]) ) {
				$onlyPV = TRUE;
				$this->query('UPDATE `' . $this->dbprefix('app_href_share_hour_summary') . '`'
						. ' SET `sc`=`sc`+1 WHERE `id`=' . intval($id));
			} else {
				$this->query('UPDATE `' . $this->dbprefix('app_href_share_hour_summary') . '`'
						. ' SET `sc`=`sc`+1, `su`=`su`+1  WHERE `id`=' . intval($id));
			}
		} else {
			$this->name('w_app_href_share_hour_summary')->insert( array(
				'token' => $token_hour,
				'href_id' => $href['href_id'],
				'time' => $hour,
				'sc' => 1,
				'su' => 1
			));
		}

		$today = date('Y-m-d', $time);
		$token_today = md5($href['id'] . $today);
		$sql = 'SELECT `id` FROM `' . $this->dbprefix('app_href_share_day_summary') . '`'
				. ' WHERE `token` = ' . $this->getConnection()->quote($token_today);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			$id = array_pop($data[0]);

			if ($onlyPV) {
				$this->query('UPDATE `' . $this->dbprefix('app_href_share_day_summary') . '`'
						. ' SET `sc`=`sc`+1 WHERE `id`=' . intval($id));
				return;
			}

			$from = strtotime($today);
			$to = strtotime(date('Y-m-d', strtotime($today . ' +1 days')));
			$sql = 'SELECT `id` FROM `' . $this->dbprefix('js_share_href') . '`'
					. ' WHERE `token`=' . $this->getConnection()->quote($token_href)
					. ' AND `time`>' . intval($from * 1000)
					. ' AND `time`<' . intval($to * 1000)
					. ' LIMIT 1';
			$data = $this->query($sql);

			if ( isset($data[0]) ) {
				$this->query('UPDATE `' . $this->dbprefix('app_href_share_day_summary') . '`'
						. ' SET `sc`=`sc`+1 WHERE `id`=' . intval($id));
			} else {
				$this->query('UPDATE `' . $this->dbprefix('app_href_share_day_summary') . '`'
						. ' SET `sc`=`sc`+1, `su`=`su`+1  WHERE `id`=' . intval($id));
			}
		} else {
			$this->name('w_app_href_share_day_summary')->insert(array(
				'token' => $token_today,
				'href_id' => $href['href_id'],
				'time' => $today,
				'sc' => 1,
				'su' => 1
			));
		}

		$this->name('w_js_share_href')->insert($insert);
	}

	private function get_ja_table($time) {
		$tablename = $this->dbprefix( 'ja_' . date('Ym', $time) );
		$exist = $this->query("show tables like '$tablename'");  
		if ( !$exist  ) {
			$this->query('CREATE TABLE `' . $tablename . '` ('
					. '`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,'
					. '`token` varchar(32) NOT NULL DEFAULT \'\','
					. '`uniq` varchar(32) NOT NULL DEFAULT \'\','
					. '`fid` bigint(20) unsigned NOT NULL DEFAULT \'0\','
					. '`href` text,'
					. '`time` bigint(20) DEFAULT NULL,'
					. '`from_ip` varchar(50) DEFAULT NULL,'
					. '`latitude` float DEFAULT NULL,'
					. '`longitude` float DEFAULT NULL,'
					. '`location` text,'
					. '`navigator` varchar(255) DEFAULT NULL,'
					. '`version` varchar(50) DEFAULT NULL,'
					. '`language` varchar(255) DEFAULT NULL,'
					. '`platform` varchar(255) DEFAULT NULL,'
					. '`user_agent` text CHARACTER SET utf8mb4,'
					. '`java_enabled` varchar(32) DEFAULT NULL,'
					. '`screen` varchar(32) DEFAULT NULL,'
					. '`screen_available` varchar(32) DEFAULT NULL,'
					. '`color_depth` varchar(50) DEFAULT NULL,'
					. '`charset` varchar(50) DEFAULT NULL,'
					. '`cookie` text,'
					. '`referrer` text,'
					. '`title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,'
					. 'PRIMARY KEY (`id`),'
					. 'KEY `INDEX_TOKEN_TIME` (`token`,`time`)'
					. ') ENGINE=InnoDB DEFAULT CHARSET=utf8');
		}
		return $tablename;
	}

	private function get_ja_href_table($time) {
		$tablename = $this->dbprefix( 'ja_href_' . date('Ym', $time) );
		$exist = $this->query("show tables like '$tablename'");  
		if ( !$exist  ) {
			$this->query('CREATE TABLE `' . $tablename . '` ('
					. '`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,'
					. '`token` varchar(32) NOT NULL DEFAULT \'\','
					. '`uniq` varchar(32) NOT NULL DEFAULT \'\','
					. '`fid` bigint(20) unsigned NOT NULL DEFAULT \'0\','
					. '`href` text,'
					. '`time` bigint(20) DEFAULT NULL,'
					. '`from_ip` varchar(50) DEFAULT NULL,'
					. '`latitude` float DEFAULT NULL,'
					. '`longitude` float DEFAULT NULL,'
					. '`location` text,'
					. '`navigator` varchar(255) DEFAULT NULL,'
					. '`version` varchar(50) DEFAULT NULL,'
					. '`language` varchar(255) DEFAULT NULL,'
					. '`platform` varchar(255) DEFAULT NULL,'
					. '`user_agent` text CHARACTER SET utf8mb4,'
					. '`java_enabled` varchar(32) DEFAULT NULL,'
					. '`screen` varchar(32) DEFAULT NULL,'
					. '`screen_available` varchar(32) DEFAULT NULL,'
					. '`color_depth` varchar(50) DEFAULT NULL,'
					. '`charset` varchar(50) DEFAULT NULL,'
					. '`cookie` text,'
					. '`referrer` text,'
					. '`title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,'
					. 'PRIMARY KEY (`id`),'
					. 'KEY `INDEX_TOKEN_TIME` (`token`,`time`)'
					. ') ENGINE=InnoDB DEFAULT CHARSET=utf8');
		}
		return $tablename;
	}

	public function read($href, $visitor, $source, $time = NULL) {
		$parent_node = array();
		$parent_node['id'] = 0;

		if (!$time) {
			$time = time();
		}

		if ($source) {
			AGAIN_SOURCE:
			$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_node') . '`'
					. ' WHERE `app_id` = ' . intval($href['id'])
					. ' AND `follower_id` = ' . intval($source['id']);
			$data = $this->query($sql);
			// 仅在用户当前阅读的是自己分享出去的链接时使用
			$update_href = array();
			$update = array();

			if ( isset( $data[0] ) ) {
				$node = $data[0];
				$update_href[] = '`view_count`=`view_count`+1';
			} else {
				$node = $this->build_node($href, $source, self::TYPE_READ);
				if( $node === 0 ) {
					goto AGAIN_SOURCE;
				}
				$this->build_relation($node, $parent_node);

				$update_href[] = '`view_count`=`view_count`+1';
				$update_href[] = '`unique_visitor`=`unique_visitor`+1';
				$update_href[] = '`affected_count`=`affected_count`+1';
			}
			if ($visitor['id'] == $source['id']) {
				// 若用户当前阅读的是自己分享出去的链接
				// 只更新被阅读数, 不需要更新关系数据
				$update[] = '`view_count`=`view_count`+1';
				$update[] = '`ip_addr`=' . $this->getConnection()->quote($href['ip_addr']);
				$update[] = '`location`=' . $this->getConnection()->quote($href['location']);

				if ($href['score'] > 0) {
					// 累加评分，对象仅为当前节点
					$update[] = '`score`=`score`+' . intval($href['score']);
				}

				$this->update_node($node, $update, $time);
				$this->update_href($href, $update_href);

				return $node['id'];
			}
			// 设置当前父节点
			$parent_node = $node;
		}

		$update_href = array();
		$update_href[] = '`view_count`=`view_count`+1';

		AGAIN:
		$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_node') . '`'
				. ' WHERE `app_id` = ' . intval($href['id'])
				. ' AND `follower_id` = ' . intval($visitor['id']);
		$data = $this->query($sql);
		if ( isset($data[0]) ) {
			$node = $data[0];
		} else {
			$node = $this->build_node($href, $visitor, self::TYPE_READ);
			if( $node === 0 ) {
				goto AGAIN;
			}
			$update_href[] = '`unique_visitor`=`unique_visitor`+1';
		}

		$level = array();
		$update = array();
		$update[] = '`ip_addr`=' . $this->getConnection()->quote($href['ip_addr']);
		$update[] = '`location`=' . $this->getConnection()->quote($href['location']);

		if ($href['score'] > 0) {
			// 累加评分，对象仅为当前节点
			$update[] = '`score`=`score`+' . intval($href['score']);
		}
		// 更新当前节点
		$this->update_node($node, $update, $time);

		if ($parent_node['id'] > 0) {
			// 取得上一级节点的层级关系
			$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_node_level') . '` nl'
					. ' INNER JOIN `' . $this->dbprefix('analysis_network_node') . '` nn ON nn.`id`=nl.`parent_id`'
					. ' WHERE nl.`node_id` = ' . intval($parent_node['id']);
			$level = $this->query($sql);
		}

		$parent_update = array();
		$parent_update[] = '`type`=' . intval(self::TYPE_FORWARD);
		$parent_update[] = '`view_count`=`view_count`+1';

		if ($this->build_relation($node, $parent_node, $level)) {
			$parent_update[] = '`affected_count`=`affected_count`+1';
			$update_href[] = '`affected_count`=`affected_count`+1';
			$node['nearest_parent_id'] = $parent_node['id'];
			$this->protection_stack = array();

			// 更新上一级节点
			$this->update_node($parent_node, $parent_update, $time);
			// 更新关系数据
			$this->update_relation($node, $level, $time);
		} elseif ($parent_node['id'] > 0) {
			$this->update_node($parent_node, $parent_update, $time);
		}

		$this->update_href($href, $update_href);

		return $node['id'];
	}

	public function read_href($href, $visitor, $source, $time = NULL) {
		$parent_node = array();
		$parent_node['id'] = 0;

		if (!$time) {
			$time = time();
		}
		if ($source) {
			AGAIN_SOURCE:
			$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_href_node') . '`'
					. ' WHERE `href_id` = ' . intval($href['href_id'])
					. ' AND `follower_id` = ' . intval($source['id']);
			$data = $this->query($sql);
			// 仅在用户当前阅读的是自己分享出去的链接时使用
			$update_href = array();
			$update = array();

			if ( isset( $data[0] ) ) {
				$node = $data[0];
				$update_href[] = '`view_count`=`view_count`+1';
			} else {
				$node = $this->build_href_node($href, $source, self::TYPE_READ);
				if( $node === 0 ) {
					goto AGAIN_SOURCE;
				}
				$this->build_href_relation($node, $parent_node);

				$update_href[] = '`view_count`=`view_count`+1';
				$update_href[] = '`unique_visitor`=`unique_visitor`+1';
				$update_href[] = '`affected_count`=`affected_count`+1';
			}
			if ($visitor['id'] == $source['id']) {
				// 若用户当前阅读的是自己分享出去的链接
				// 只更新被阅读数, 不需要更新关系数据
				$update[] = '`view_count`=`view_count`+1';
				$update[] = '`ip_addr`=' . $this->getConnection()->quote($href['ip_addr']);
				$update[] = '`location`=' . $this->getConnection()->quote($href['location']);

				if ($href['score'] > 0) {
					// 累加评分，对象仅为当前节点
					$update[] = '`score`=`score`+' . intval($href['score']);
				}

				$this->update_href_node($node, $update, $time);
				$this->update_href_href($href, $update_href);

				return $node['id'];
			}
			// 设置当前父节点
			$parent_node = $node;
		}

		$update_href = array();
		$update_href[] = '`view_count`=`view_count`+1';

		AGAIN:
		$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_href_node') . '`'
				. ' WHERE `href_id` = ' . intval($href['href_id'])
				. ' AND `follower_id` = ' . intval($visitor['id']);
		$data = $this->query($sql);

		if ( isset($data[0]) ) {
			$node = $data[0];
		} else {
			$update_href[] = '`unique_visitor`=`unique_visitor`+1';
			$node = $this->build_href_node($href, $visitor, self::TYPE_READ);
			if( $node === 0 ) {
				goto AGAIN;
			}
		}

		$level = array();
		$update = array();
		$update[] = '`ip_addr`=' . $this->getConnection()->quote($href['ip_addr']);
		$update[] = '`location`=' . $this->getConnection()->quote($href['location']);

		if ($href['score'] > 0) {
			// 累加评分，对象仅为当前节点
			$update[] = '`score`=`score`+' . intval($href['score']);
		}
		// 更新当前节点
		$this->update_href_node($node, $update, $time);

		if ($parent_node['id'] > 0) {
			// 取得上一级节点的层级关系
			$sql = 'SELECT * FROM `' . $this->dbprefix('analysis_network_href_node_level') . '` nl'
					. ' INNER JOIN `' . $this->dbprefix('analysis_network_href_node') . '` nn ON nn.`id`=nl.`parent_id`'
					. ' WHERE nl.`node_id` = ' . intval($parent_node['id']);
			$level = $this->query($sql);
		}

		$parent_update = array();
		$parent_update[] = '`type`=' . intval(self::TYPE_FORWARD);
		$parent_update[] = '`view_count`=`view_count`+1';

		if ($this->build_href_relation($node, $parent_node, $level)) {
			$parent_update[] = '`affected_count`=`affected_count`+1';
			$update_href[] = '`affected_count`=`affected_count`+1';
			$node['nearest_parent_id'] = $parent_node['id'];
			$this->protection_stack = array();

			// 更新上一级节点
			$this->update_href_node($parent_node, $parent_update, $time);
			// 更新关系数据
			$this->update_href_relation($node, $level, $time);
		} elseif ($parent_node['id'] > 0) {
			$this->update_href_node($parent_node, $parent_update, $time);
		}

		$this->update_href_href($href, $update_href);

		return $node['id'];
	}

	public function update_href($href, $update) {
		if (count($update)) {
			$this->query('UPDATE `' . $this->dbprefix('analysis_app') . '`'
							. ' SET ' . implode(', ', $update) . ' WHERE `id`=' . intval($href['id']));
		}
	}

	public function update_href_href($href, $update) {
		if (count($update)) {
			$this->query('UPDATE `' . $this->dbprefix('analysis_app_href') . '`'
							. ' SET ' . implode(', ', $update) . ' WHERE `id`=' . intval($href['href_id']));
		}
	}

	public function getAnalysisAppById($id) {
		$id = intval($id);
		$sql = "SELECT * from w_analysis_app where id = $id";
		$data = $this->query($sql);
		return array_pop($data);
	}

	public function getAppHrefById($id) {
		$id = intval($id);
		$sql = "SELECT * from w_analysis_app_href where id = $id";
		$data = $this->query($sql);
		return array_pop($data);
	}

	public function getAppHrefByAppIdHref($app_id, $url) {
		$id = intval($app_id);
		$url = trim($url);
		$sql = "SELECT * from w_analysis_app_href where app_id = :app_id and url = :url limit 1";
		$data = $this->query($sql, [ 'app_id' => $app_id, 'url' => $url]);
		return array_pop($data);
	}

	public function getAppHrefByAppId($app_id) {
		$id = intval($app_id);
		$sql = "SELECT * from w_analysis_app_href where app_id = :app_id";
		$data = $this->query($sql, [ 'app_id' => $app_id]);
		return $data;
	}

	public function getAppHref($query = []) {
		$limit = 20;
		$offset = false;
		if( isset($query['limit']) ) $limit = intval($query['limit']);
		if( isset($query['offset']) ) $offset = intval($query['offset']);

		$arrField = ['id','title'];
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

		if( isset($query['title']) ) {
			$query['title'] = '%'.trim($query['title']).'%';
			$where[] = ' and title like :title ';
		}

		$sql = "SELECT * from w_analysis_app_href where 1" . implode(' ', $where);
		$data = $this->query($sql,$query);
		return $data;
	}

	public function getApp($query = []) {
		$limit = 20;
		$offset = false;
		if( isset($query['limit']) ) $limit = intval($query['limit']);
		if( isset($query['offset']) ) $offset = intval($query['offset']);

		$arrField = ['id','title'];
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

		if( isset($query['title']) ) {
			$query['title'] = '%'.trim($query['title']).'%';
			$where[] = ' and title like :title ';
		}

		$sql = "SELECT * from w_analysis_app where 1" . implode(' ', $where);
		$data = $this->query($sql,$query);
		return $data;
	}

}

