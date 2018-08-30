<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/8/6
 * Time: 9:36
 */
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
class GetPhoneNumberLocation extends Command{
	public $pssa4gSvc = 'http://121.31.255.116:9001/uop4g/services/Open';
	public  $baseUrl = 'http://apis.juhe.cn/mobile/get';
	public $mobile = "?phone=";
	public $endParam = "&dtype=json&key=99d9d61a5a72944c1affd0b6d7fa42b2";
	public $city = ["南宁"=>591,"柳州"=>593,"桂林"=>592,"玉林"=>595,"北海"=>599,"钦州"=>597,"防城港"=>590
	,"贵港"=>589,"崇左"=>600,"河池"=>598,"梧州"=>594,"来宾"=>601,"百色"=>596,"贺州"=>588];
	protected function configure()
	{
		$this->setName('location')->setDescription('Here is the remark ');
	}

	protected function execute(Input $input, Output $output)
	{
		$output->writeln("CommandStart:");
		$mysqli = new \mysqli('127.0.0.1','root','205a0401cb4757c6','test','3306');
		if($mysqli->connect_error){
			die($mysqli->connect_error);
		}

		$countSql = "SELECT uid as maxId FROM `ims_mc_members` WHERE mobile!=''  order by uid desc limit 1;";
		$mysqli -> query('set names utf8');
		$query = $mysqli -> query($countSql);
		while($rs=mysqli_fetch_array($query)){
			$result[]=$rs;
		}


		if(empty($result[0]["maxId"])){
			echo "当前无数据！";
			exit;
		}
		$maxId = $result[0]["maxId"];
		$curId = 0;
		while($curId<=$maxId){
			//当前取出五百个数据
			$sql = "SELECT uid,mobile  FROM `ims_mc_members` WHERE mobile!=''and  phonenum_city_id =0 order by uid asc limit 500;";
			$mysqli -> query('set names utf8');
			$queryData = $mysqli -> query($sql);
			while($rs=mysqli_fetch_array($queryData)){
				$resultData[]=$rs;
			}
			//取完数据后，通过接口更新数据
			print_r($resultData);
			foreach($resultData as $item){
				//$url = $this->baseUrl.$this->mobile.$item["mobile"].$this->endParam;
				//$result = $this->getCurl($url);
				$userBrand = $this->userNumberCheck($item["mobile"]);
				print_r($userBrand);
				$citycode = $userBrand["data"]["checkresult"]["citycode"];
				sleep(5);
				print_r($citycode);
				if(!empty($citycode)){

					try{
						$code = $citycode;
						$updateSql = "UPDATE ims_mc_members SET phonenum_city_id =".$code." WHERE uid =".$item["uid"];
						$queryData = $mysqli -> query($updateSql);
						$curId = $item["uid"];
					}catch (Excetion $e){
						print_r("无此城市数据");
						$curId = $maxId+9999;
					}
				}
			}

		}
		$mysqli->close();
		$output->writeln("CommandEnd");
	}


	/**get请求
	 * @param $url
	 * @return mixed
	 */
	 public function getCurl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //https请求 不验证证书 其实只用这个就可以了
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //https请求 不验证HOST
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

//查询品牌
	public function userNumberCheck($tel)
	{

		$result = $this->auserNumberCheckT($tel);
		return json_decode($result, true);
	}

	public function auserNumberCheckT($tel){

		$client = self::getSoapClient($this->pssa4gSvc);
		$rtn = $client->usernumbercheck($tel);
		return $rtn;
	}


	public  function getSoapClient($location){
		$simple = new \SoapClient(null, array('location'=>$location,'uri'=>'bss.ws.api.uop.sxit.com','encoding'=>'UTF-8','cache_wsdl' => WSDL_CACHE_NONE));
		$auth = new ReqSOAPHeader("chengmei", "ChengM@171020");
		$header = new \SoapHeader('http://api.uop.com', 'ReqSOAPHeader', $auth);
		$simple->__setSoapHeaders($header);
		return $simple;
	}

}
class ReqSOAPHeader {
	public $appKey;
	public $authKey;

	public function __construct($app,$auth) {
		$this->appKey=$app;
		$this->authKey=$auth;
	}
}

