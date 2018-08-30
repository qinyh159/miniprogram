<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
	public function index()
	{
		/*$appid="d963beadf1354b05ae77bead49a04a3b";
		$linkurl="http://union-click.jd.com/jdc?e=0&p=AyIHZRtbFAITA1AeXxYyEgFQH1McCxYGUB9rUV1KWQorAlBHUwxLBQNQVk4YDk5ER1xOGVUdXhEKGw5RGl4RHUtCCUZrQUYRTlVsE15gFEclWwRxfHZOMUswQw4eaVYaWxweEwFJGl4EAhUMURhQFwYiBmUbWhQDFANWElIUMiIHVSsaewISD1YcXSUCEABRH1ITBxcPZRtfFwMVD1AZXxELGg5lHGvCpZPfxZ%2BOmJPGnPYraxUEFwNdElIRAxcDZStrJQIiBGVZNRYDEg5UHVoQbBAAURJZFQYU&t=W1dCFBBFC1pXUwkEAEAdQFkJBVsTBxYPXBJfFAcWGAxeB0g%3D";
		$linkurl=urlencode($linkurl);
		$kw=urlencode('gxltwx');
		$url="https://coupon.m.jd.com/union?mtm_source=kepler-m&mtm_subsource=$appid&mopenbp5=$kw&returl=$linkurl";
		echo $url;*/

		$img = imagecreatetruecolor(100, 40);
		$black = imagecolorallocate($img, 0x00, 0x00, 0x00);
		$green = imagecolorallocate($img, 0x00, 0xFF, 0x00);
		$white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
		imagefill($img, 0, 0, $white);
		//生成随机的验证码
		$code = '';
		for ($i = 0; $i < 4; $i++) {  //4位数的验证码
			$code .= rand(0, 9);
		}
		imagestring($img, 5, 10, 10, $code, $black);
		//加入噪点干扰
		for ($i = 0; $i < 50; $i++) {
			imagesetpixel($img, rand(0, 100), rand(0, 100), $black);  //imagesetpixel — 画一个单一像素，语法: bool imagesetpixel ( resource $image , int $x , int $y , int $color )
			imagesetpixel($img, rand(0, 100), rand(0, 100), $green);
		}
		//输出验证码
		header("content-type: image/png");
		imagepng($img);  //以 PNG 格式将图像输出到浏览器或文件
		//imagedestroy($img);  //图像处理完成后，使用 imagedestroy() 指令销毁图像资源以释放内存，虽然该函数不是必须的，但使用它是一个好习惯。
		//return $this->fetch("test/test2");
	}
}
