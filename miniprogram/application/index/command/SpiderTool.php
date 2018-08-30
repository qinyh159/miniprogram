<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/7/11
 * Time: 16:03
 */
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;


class SpiderTool extends Command
{
	protected function configure()
	{
		$this->setName('spider')->setDescription('Here is the remark ');
	}

	protected function execute(Input $input, Output $output)
	{
		$output->writeln("CommandStart:");
		$redis = new \Redis();
		//$redis->connect('127.0.0.1',6379);
		print_r(extension_loaded("redis"));
		$output->writeln("CommandEnd");
	}
}