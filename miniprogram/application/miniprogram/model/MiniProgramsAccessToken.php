<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/2/25
 * Time: 12:06
 */
namespace app\miniprogram\Model;


use app\miniprogram\common\Model;
use app\miniprogram\util\NetworkUtil;

class MiniProgramsAccessToken extends Model
{
    const TOKEN_EXPIRE_IN = 7000;
	var $table = "mini_programs_access_token";

	static public function getToken($appid)
	{

		$model = new \app\miniprogram\common\Model\MiniProgramsAccessToken();

		$sql = "select * from mini_programs_access_token where appid = :appid";

		return $model->db()->query($sql, ["appid" => $appid]);
	}

	static public function insertToken($appid, $accesstoken)
	{

		$model = new \app\miniprogram\common\Model\MiniProgramsAccessToken();
		$model->db()->insert(["appid" => $appid, "time" => time(), "accesstoken" => $accesstoken]);
	}

	static public function updateToken($id, $accesstoken)
	{
		$model = new \app\miniprogram\common\Model\MiniProgramsAccessToken();

		$model->db()->where("id", $id)->update([
			"time" => time(),
			"accesstoken" => $accesstoken
		]);
	}


	public function getTokenByAppId($appid)
	{
		$appid = (int)$appid;
		$model = new \app\miniprogram\common\Model\MiniProgramsAccessToken();
		$sql = "SELECT * from mini_programs_access_token where appid = :appid limit 1";
		$tokenInfo = $model->db()->query($sql, ['appid' => $appid]);
		return $tokenInfo;
	}

    /**
     * 获取数据Token并更新
     * 1.获取Token如果时间未过期直接返回
     * 2.如果Token已经过去 , 请求微信服务器获取Token再更新到数据库
     */
    public static function getTokenAndUpdate($id)
    {
        $app = MiniProgramsApplist::getOneById($id);
        if (empty($app)){
            throw new Exception('获取AccessToken异常');
        }

        if (time() - $app['time'] > self::TOKEN_EXPIRE_IN){
            $url = config('wx.access_token_url');
            $url = sprintf($url, $app['appid'], $app['appsecret']);

            $token = NetworkUtil::getCurl($url);
            $token = json_decode($token, true);
            if (!$token)
            {
                throw new Exception('获取AccessToken异常');
            }
            if(!empty($token['errcode'])){
                throw new Exception($token['errmsg']);
            }

            self::where("appid", $id)->update([
                "time" => time(),
                "accesstoken" => $token['access_token']
            ]);

            return $token['access_token'];
        } else {
          return $app['token'] ;
        }
	}
}
