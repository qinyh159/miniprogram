<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/7/20
 * Time: 13:05
 */
namespace app\mini\controller;

use phpspider\core\requests;
use think\Controller;
use think\Request;
class Test extends Controller
{
	public function Index(){
		$request = Request::instance();
		$sharId = $request->get('shar_id');
		$data[0] = ["id"=>1,
				"img"=>"http://puui.qpic.cn/qqvideo_ori/0/d0338221rk2_496_280/0",
				"video"=>"http://ugcbsy.qq.com/uwMRJfz-r5jAYaQXGdGnC2_ppdhgmrDlPaRvaV7F2Ic/y0350bkahu8.mp4?vkey=6361D57A98A2BC94ED7F3198ED7104C888439B6D339C26A924488B4D99CD2F9246B62AE137C318001C015E9351E0024CDDC7B75F6DF27181714E3B0804B9CD335FD74002B740ED87E3AE638800363751A3641CF7D1439613E4E3699885340DF7091E58EDC50B8567BD88CCFC5A97C06567D180BA5F9BCC00&br=60&platform=2&fmt=auto&level=0&sdtfrom=v3010&guid=257ca7992c4a27484375211eee72b727",
				"text"=>"一场比一场高能！希拉里特朗普三度辩论“互撕”",
				"duration"=>"04:09",
				"date" => "2016年10月20日",
				"click_times" => "27.7万播放",
				"mark_num" => ""
		];
		$data[1] = ["id"=>2,
				"img"=>"http://puui.qpic.cn/qqvideo_ori/0/k0338vcpnrv_496_280/0",
				"text"=>"3分钟看完美国第三场总统辩论：怎么还骂人呢",
				"video"=>"http://ugcbsy.qq.com/uwMRJfz-r5jAYaQXGdGnC2_ppdhgmrDlPaRvaV7F2Ic/k0338vcpnrv.mp4?vkey=6CDB1B95898E9D6F447EDC06486029CC4021565A2DB258F7BF8D3D9F7D4DD190648722EE41761F6A5CBA6C436181209D799F80364660793CFC2DF2E8043B906D50DD3EEAF34EFC20A83F14341ADF2CDDB109FC453A4AC7FF9B4767874E8EFEBFB647B91A0851F9D94998BB32184066171B19EBF9C067C116&br=60&platform=2&fmt=auto&level=0&sdtfrom=v3010&guid=257ca7992c4a27484375211eee72b727",
				"duration"=>"03:34",
				"date" => "2016年10月20日",
				"click_times" => "127.3万播放",
				"mark_num" => "17条评论"
		];
		$data[2] = ["id"=>3,
				"img"=>"http://puui.qpic.cn/qqvideo_ori/0/v0335sukyx7_496_280/0",
				"video"=>"http://211.97.73.148/vkp.tc.qq.com/A3995lAXsg4gnHF-NZtG8s9UFdiZKr1_R-FEucLB9pa0/b0021g7ebxj.mp4?vkey=91643723B3FB718E049A86AECC88191FBD4D6788A6B26E18B8B2FA779ECC42F912BEE5CE2D2F799BC79E9C3D79828E5BC377AC4CB5E434ECDE6AC77E27E1E5796986CE6FFECAFAD9C1CB7FC035A37A28AF437DF4A657E8BD64A35F3DCC1ED528BF561D696A5F638A04C69BE269CD78B2E48BF8C4F53367F6&br=60&platform=2&fmt=auto&level=0&sdtfrom=v3010&guid=257ca7992c4a27484375211eee72b727",
				"text"=>"3分钟看完美国大选副总统辩论：不能好好聊天吗",
				"duration"=>"03:25",
				"date" => "2016年10月11日",
				"click_times" => "34.2万播放",
				"mark_num" => ""
		];
		$data[3] = ["id"=>4,
				"img"=>"http://puui.qpic.cn/qqvideo_ori/0/q0335myukvw_496_280/0",
				"text"=>"希拉里和特朗普今天的互撕 看这个视频就够了",
				"video"=>"http://ugcbsy.qq.com/uwMRJfz-r5jAYaQXGdGnC2_ppdhgmrDlPaRvaV7F2Ic/k0338vcpnrv.mp4?vkey=6CDB1B95898E9D6F447EDC06486029CC4021565A2DB258F7BF8D3D9F7D4DD190648722EE41761F6A5CBA6C436181209D799F80364660793CFC2DF2E8043B906D50DD3EEAF34EFC20A83F14341ADF2CDDB109FC453A4AC7FF9B4767874E8EFEBFB647B91A0851F9D94998BB32184066171B19EBF9C067C116&br=60&platform=2&fmt=auto&level=0&sdtfrom=v3010&guid=257ca7992c4a27484375211eee72b727",
				"duration"=>"03:25",
				"date" => "2016年10月10日",
				"click_times" => "59.7万播放",
				"mark_num" => "1条评论"
		];
		$data[4] = ["id"=>5,
				"img"=>"http://puui.qpic.cn/qqvideo_ori/0/l0335qmul7e_496_280/0",
				"video"=>"http://ugcws.video.gtimg.com/uwMRJfz-r5jAYaQXGdGnC2_ppdhgmrDlPaRvaV7F2Ic/l0335qmul7e.mp4?vkey=44C0B681B27B4E8009426513358C6552B779928C1CF5290EBBF3783CABF643DC5B9109E314C0C5405E7BBF456F973DE00B17E5D5E5F3CA1C181F8BFCA04A846F5336F530DD7C0FC0219C280C25641141C40A5DC5389C371266197B80F4349920AC2EA74FE6B3EBEF20B36048D9E37DDD0176BD6D0031DD9E&br=60&platform=2&fmt=auto&level=0&sdtfrom=v3010&guid=257ca7992c4a27484375211eee72b727",
				"text"=>"希拉里特朗普对撕：3分钟看完美国大选第一场辩论",
				"duration"=>"03:06",
				"date" => "2016年10月10日",
				"click_times" => "28.6万播放",
				"mark_num" => "1条评论"
		];

		if(!empty($sharId)){
			//通过sql找出指定id信息

			//通过sql找出非指定id信息

			//融合数组
		}


		$this->assign("dataList", $data);
		return $this->fetch('index/index');
	}


	/**
	 * 获取物流信息
	 */
	public function  getLogisticsInfo(){

		$postId = "8095810371";
		$comResult = $this->getCurl("http://www.kuaidi100.com/autonumber/autoComNum?text=".$postId);
		$comArr = json_decode($comResult,true);
		$curCommpany = "";
		$curLogisticsInfo = "";
		if(empty($comArr["auto"])){
			return;
		}
		foreach($comArr["auto"] as $key => $value){
			$commpany = $value["comCode"];
			$result = $this->getCurl("http://www.kuaidi100.com/query?type=".$commpany."&postid=".$postId);
			$dataArr = json_decode($result,true);
			if($dataArr["status"]==200&&!empty($dataArr["data"])){
				$curCommpany = $dataArr["com"];
				$curLogisticsInfo = $dataArr["data"];
			}

		}
		$curCommpany = $this->get_express_company($curCommpany);
		$this->assign("curCommpany", $curCommpany);
		$this->assign("curLogisticsInfoList", $curLogisticsInfo);
		return $this->fetch('index/getLogisticsInfo');


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
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36 Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //https请求 不验证证书 其实只用这个就可以了
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //https请求 不验证HOST
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	function get_express_company($typeCom)
	{
		if ($typeCom == 'aae'){
			$typeCom = 'AAE全球专递';
		}elseif ($typeCom == 'anjiekuaidi'){
			$typeCom = '安捷快递';
		}elseif ($typeCom == 'anxindakuaixi'){
			$typeCom = '安信达快递';
		}elseif ($typeCom == 'baifudongfang'){
			$typeCom = '百福东方';
		}elseif ($typeCom == 'biaojikuaidi'){
			$typeCom = '彪记快递';
		}elseif ($typeCom == 'bht'){
			$typeCom = 'BHT';
		}elseif ($typeCom == 'cces'){
			$typeCom = '希伊艾斯快递';
		}elseif ($typeCom == 'coe'){
			$typeCom = '中国东方';
		}elseif ($typeCom == 'changyuwuliu'){
			$typeCom = '长宇物流';
		}elseif ($typeCom == 'datianwuliu'){
			$typeCom = '大田物流';
		}elseif ($typeCom =='debangwuliu'){
			$typeCom =  '德邦物流';
		}elseif ($typeCom == 'dpex'){
			$typeCom = 'DPEX';
		}elseif ($typeCom == 'dhl'){
			$typeCom = 'DHL';
		}elseif ($typeCom == 'dsukuaidi'){
			$typeCom = 'D速快递';
		}elseif ($typeCom == 'fedex'){
			$typeCom = 'fedex';
		}elseif ($typeCom == 'feikangda'){
			$typeCom = '飞康达物流';
		}elseif ($typeCom == 'fenghuangkuaidi'){
			$typeCom = '凤凰快递';
		}elseif ($typeCom == 'ganzhongnengda'){
			$typeCom = '港中能达物流';
		}elseif ($typeCom == 'guangdongyouzhengwuliu'){
			$typeCom = '广东邮政物流';
		}elseif ($typeCom == 'huitongkuaidi'){
			$typeCom = '汇通快运';
		}elseif ($typeCom == 'hengluwuliu'){
			$typeCom = '恒路物流';
		}elseif ($typeCom == 'huaxialongwuliu' ){
			$typeCom ='华夏龙物流';
		}elseif ($typeCom == 'jiayiwuliu' ){
			$typeCom ='佳怡物流';
		}elseif ($typeCom == 'jinguangsudikuaijian'){
			$typeCom = '京广速递';
		}elseif ($typeCom == 'jixianda'){
			$typeCom = '急先达';
		}elseif ($typeCom == 'jiajiwuliu'){
			$typeCom = '佳吉物流';
		}elseif ($typeCom == 'jiayunmeiwuliu'){
			$typeCom = '加运美';
		}elseif ($typeCom == 'kuaijiesudi'){
			$typeCom = '快捷速递';
		}elseif ($typeCom == 'lianhaowuliu'){
			$typeCom = '联昊通物流';
		}elseif ($typeCom ==  'longbanwuliu'){
			$typeCom ='龙邦物流';
		}elseif ($typeCom == 'minghangkuaidi'){
			$typeCom = '民航快递';
		}elseif ($typeCom == 'peisihuoyunkuaidi'){
			$typeCom = '配思货运';
		}elseif ($typeCom =='quanchenkuaidi' ){
			$typeCom = '全晨快递';
		}elseif ($typeCom == 'quanjitong'){
			$typeCom = '全际通物流';
		}elseif ($typeCom =='quanritongkuaidi' ){
			$typeCom = '全日通快递';
		}elseif ($typeCom == 'quanyikuaidi'){
			$typeCom = '全一快递';
		}elseif ($typeCom == 'shenghuiwuliu'){
			$typeCom = '盛辉物流';
		}elseif ($typeCom == 'suer'){
			$typeCom = '速尔物流';
		}elseif ($typeCom == '盛丰物流'){
			$typeCom = 'shengfengwuliu';
		}elseif ($typeCom == 'tiandihuayu'){
			$typeCom = '天地华宇';
		}elseif ($typeCom == '天天'){
			$typeCom = 'tiantian';
		}elseif ($typeCom == 'tnt'){
			$typeCom = 'TNT';
		}elseif ($typeCom == 'ups'){
			$typeCom = 'UPS';
		}elseif ($typeCom =='wanjiawuliu' ){
			$typeCom = '万家物流';
		}elseif ($typeCom =='wenjiesudi'){
			$typeCom =  '文捷航空速递';
		}elseif ($typeCom == 'wuyuansudi'){
			$typeCom = '伍圆速递';
		}elseif ($typeCom =='wanxiangwuliu'){
			$typeCom = '万象物流';
		}elseif ($typeCom == 'xinbangwuliu'){
			$typeCom = '新邦物流';
		}elseif ($typeCom == 'xinfengwuliu'){
			$typeCom = '信丰物流';
		}elseif ($typeCom =='xingchengjibian'){
			$typeCom =  '星晨急便';
		}elseif ($typeCom == 'xinhongyukuaidi'){
			$typeCom = '鑫飞鸿物流快递';
		}elseif ($typeCom == 'yafengsudi'){
			$typeCom = '亚风速递';
		}elseif ($typeCom == 'yibangwuliu'){
			$typeCom = '一邦速递';
		}elseif ($typeCom == 'youshuwuliu'){
			$typeCom = '优速物流';
		}elseif ($typeCom ==  'yuanchengwuliu'){
			$typeCom ='远成物流';
		}elseif ($typeCom == 'yuantong'){
			$typeCom = '圆通速递';
		}elseif ($typeCom == '源伟丰快递'){
			$typeCom = 'yuanweifeng';
		}elseif ($typeCom == 'yuanzhijiecheng'){
			$typeCom = '元智捷诚快递';
		}elseif ($typeCom == 'yuefengwuliu'){
			$typeCom = '越丰物流';
		}elseif ($typeCom == 'yunda'){
			$typeCom = '韵达快运';
		}elseif ($typeCom == 'yuananda'){
			$typeCom = '源安达';
		}elseif ($typeCom =='yuntongkuaidi' ){
			$typeCom = '运通快递';
		}elseif ($typeCom == 'zhaijisong'){
			$typeCom = '宅急送';
		}elseif ($typeCom == 'zhongtiewuliu'){
			$typeCom = '中铁快运';
		}elseif ($typeCom =='ems'){
			$typeCom =  'EMS快递';
		}elseif ($typeCom == 'shentong'){
			$typeCom = '申通快递';
		}elseif ($typeCom == 'shunfeng'){
			$typeCom = '顺丰速运';
		}elseif ($typeCom == 'zhongyouwuliu'){
			$typeCom = '中邮物流';
		}
		return $typeCom;
	}


	/**
	 * 将集团穿过来的数据转发给wocf
	 */
	public function saveDataToWocf(){
		$request = Request::instance();
		$data = $request->param();

		$userNumber = (string)(empty($data["userNumber"]) ? "" : $data["userNumber"]);

		$rechargeTime = (string)(empty($data["rechargeTime"]) ? "" : $data["rechargeTime"]);

		$rechargeChannel = (string)(empty($data["rechargeChannel"]) ? "" : $data["rechargeChannel"]);

		$rechargeAmount = (string)(empty($data["rechargeAmount"]) ? "" : $data["rechargeAmount"]);

		$rechargeID = (string)(empty($data["rechargeID"]) ? "" : $data["rechargeID"]);

		$rechargeAreaCode = (string)(empty($data["rechargeAreaCode"]) ? "" : $data["rechargeAreaCode"]);

		$serviceType = (string)(empty($data["serviceType"]) ? "" : $data["serviceType"]);

		$loginNumber = (string)(empty($data["loginNumber"]) ? "" : $data["loginNumber"]);

		$numType = (string)(empty($data["numType"]) ? "" : $data["numType"]);

		$netType = (string)(empty($data["netType"]) ? "" : $data["netType"]);

		$state = (string)(empty($data["state"]) ? "" : $data["state"]);

		//将数据发送到wocf
		$url = "http://oto.gx10010.com/app/index.php?i=47&c=entry&do=game_jinan&m=cm_bigwheel&op=savechargelog";
		$log = array(
				'userNumber' => $userNumber,
				'rechargeTime' => $rechargeTime,
				'rechargeChannel' => $rechargeChannel,
				'rechargeAmount' => $rechargeAmount,
				'rechargeID' => $rechargeID,
				'rechargeAreaCode' => $rechargeAreaCode,
				'serviceType' => $serviceType,
				'loginNumber' => $loginNumber,
				'numType' => $numType,
				'netType' => $netType,
				'state' => $state
		);
		$result = $this->post($url,$log);
		return $result;
	}


	/**
	 * post请求
	 * @param $url
	 * @param $data
	 * @param string $cookie
	 * @param null $proxy
	 * @return bool|string
	 */
	 public function post($url, $data, $cookie = '', $proxy = null)
	{

		if (!$url) return false;
		$ssl = substr($url, 0, 8) == 'https://' ? true : false;
		$curl = curl_init();
		if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
		if (substr($url, 0, 8) == "https://") {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36 Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8");
		curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//Post提交的数据包

		// 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
		curl_setopt($curl, CURLOPT_HEADER, true);


		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($curl);
		// 获得响应结果里的：头大小
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		// 根据头大小去获取头信息内容
		$header = substr($content, 0, $headerSize);
		curl_close($curl);
		return substr($content,$headerSize);
	}

}