<?php
/**
 * Created by PhpStorm.
 * User: Alex.Ou
 * Date: 2016/6/8
 * Time: 15:56
 */

namespace app\common;

use think\Request;

class Controller extends \think\Controller
{
	protected static $allowed = [];

	public function _initialize()
	{
		$this->validationAccess();
		$this->lang();
	}

	public function validationAccess()
	{
		session_start();

		$request         = Request::instance();
		$action          = strtolower($request->action());
		$controller_name = strtolower($request->controller());

		if (!in_array($action,self::$allowed)){
			if (!isset($_SESSION['sid']) && $controller_name != 'login' && $action != 'index') {
				$this->redirect('login/index');
			}

			if ($_SESSION['email_address'] != 'admin@panmeta.com' && $_SESSION['sid'] != 1){
				$access = $_SESSION['access'];

				//判断用户权限
				if (!in_array($controller_name.'.'.$action, $access)){
						$this->error("您没有访问权限");
				}
			}
		}
	}


	public function lang() {
		$localesModel = new localesModel();
		$localesModel->init();
	}

	public function returJson($data = '',$code = 0,$msg = '')
	{
		return json_encode(['data'=>$data,'code'=>$code,'msg'=>$msg]);
	}

	public function returArr($data = '',$code = 0,$msg = '')
	{
		return ['data'=>$data,'code'=>$code,'msg'=>$msg];
	}

	/**
	 * Ajax方式返回数据到客户端
	 * @access protected
	 * @param mixed $data 要返回的数据
	 * @param String $type AJAX返回数据格式
	 * @return void
	 */
	protected function ajaxReturn($data,$type='') {
		if(func_num_args()>2) {// 兼容3.0之前用法
			$args      =  func_get_args();
			array_shift($args);
			$info      =  array();
			$info['data']  =  $data;
			$info['info']  =  array_shift($args);
			$info['status'] =  array_shift($args);
			$data      =  $info;
			$type      =  $args?array_shift($args):'';
		}
		if(empty($type)) $type =  C('DEFAULT_AJAX_RETURN');
		if(strtoupper($type)=='JSON') {
			// 返回JSON数据格式到客户端 包含状态信息
			header('Content-Type:text/html; charset=utf-8');
			exit(json_encode($data));
		}elseif(strtoupper($type)=='XML'){
			// 返回xml格式数据
			header('Content-Type:text/xml; charset=utf-8');
			exit(xml_encode($data));
		}elseif(strtoupper($type)=='EVAL'){
			// 返回可执行的js脚本
			header('Content-Type:text/html; charset=utf-8');
			exit($data);
		}else{
			// TODO 增加其它格式
		}
	}
}