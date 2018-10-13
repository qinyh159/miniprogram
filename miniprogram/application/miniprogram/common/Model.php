<?php
/**
 * Created by PhpStorm.
 * User: Alex.Ou
 * Date: 2016/6/8
 * Time: 16:01
 */

namespace app\common;


class Model extends \think\Model
{
    function startTrans($label=''){
        return $this->db()->startTrans($label);
    }
    
    function commit($label=''){
        return $this->db()->commit($label);
    }
    
    function rollback(){
        return $this->db()->rollback();
    }
}
