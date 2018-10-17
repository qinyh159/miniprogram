<?php
/**
 * Created by PhpStorm.
 * User: huihui
 * Date: 2018/2/27
 * Time: 9:56
 */
namespace app\miniprogram\Model;


use app\miniprogram\common\Model;

class MiniProgramsChatUpload extends Model
{
    public function getContentAttr($value, $data)
    {
        $content = $value;
        if ($data['content'] != null){
            $content = json_decode($data['content']);
        }
        return $content;
    }

	var $table = "mini_programs_chat_upload";

	static public function findTypeOfChatUpload($type)
	{
        $chatUploadInfo = self::where(['type' => $type])->order('id','desc')->paginate(10, true);
		return $chatUploadInfo;
	}

	static public function insertChatUpload($type,$filename,$filepath,$originalfilename,$mimetype,$filesize)
	{
		$chatUploadMode = new \app\miniprogram\common\Model\MiniProgramsChatUpload();
		$data = [
			"time" => time(),
			"type" => $type,
			"filename" => $filename,
			"filepath" => $filepath,
			"originalfilename" => $originalfilename,
			"mimetype" => $mimetype,
			"filesize"=>$filesize
		];

		return $chatUploadMode->db()->insertGetId($data);;
	}

	static public function findIdOfChatUpload($id)
	{
		$chatUploadMode = new \app\miniprogram\common\Model\MiniProgramsChatUpload();
		$sql = "SELECT * FROM `mini_programs_chat_upload`  WHERE  id=:id";
		$chatUploadInfo = $chatUploadMode->db()->query($sql, ['id' => $id]);
		return $chatUploadInfo;
	}

    static public function getChatUpdateById($id)
    {
        return self::find($id);
    }
}
