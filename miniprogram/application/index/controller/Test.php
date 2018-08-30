<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/7/17
 * Time: 9:49
 */
use think\Controller;

class Test extends Controller
{

	public function index(){


		//$this->assign("projectInfo", $projectInfo);
		return $this->fetch("test");
	}

}