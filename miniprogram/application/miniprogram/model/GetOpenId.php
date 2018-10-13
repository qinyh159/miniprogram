<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/4/4
 * Time: 9:53
 */
namespace app\miniapp\model;


use app\common\Model;

class GetOpenId extends Model
{
	var $table = "get_openid";

	public function getItemByCurUserKey($curUserKey){

		$curUserKey = (string)$curUserKey;

		$sql = "SELECT * from get_openid where curUserKey = :curUserKey ORDER  BY id desc limit 1";
		$data = $this->db()->query($sql, ['curUserKey' => $curUserKey]);
		if (empty($data)) {
			return [];
		}
		return $data[0];
	}
}
