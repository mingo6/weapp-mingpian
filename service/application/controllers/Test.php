<?php
use Sobyte\BaiduAip\AipOcr;
use Sobyte\QcloudImage\CIClient;

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function card_get()
    {
        $file = PUB_PATH . 'mingpian.jpg';
        $client = new CIClient('1251009842', 'AKIDAb6RtDzkKjy202TtZ4aLH4hARYVHNJah', '5Y7sxvkjXChHxjU4UkqyV6gO0oRBqP34', 'mingpian');
        // $client = new CIClient('10149902', 'AKIDxnS0RhMFLLE0c4CxCZs6JRvNjzEQppuo', 'mZHya6WyGUYlpogS4VmspbM4d0BGCsTu', 'mingpian');
        $client->setTimeout(30);
        $result = $client->namecardDetect(array('urls' => array('http://mingpian-1251009842.picbj.myqcloud.com/timg.jpg', 'http://yoututest-1251966477.cossh.myqcloud.com/mingpian.jpg')), 0);
        // 单个或多个图片file,
        // $result = $client->namecardDetect(['files' => [$file]]);
        // 单个或多个图片内容
        // $image = file_get_contents($file);
        // $result = $client->namecardDetect(['buffers' => [$image]], 0);
        var_dump(json_decode($result,true)['result_list']) ;
        die();
        $client = new AipOcr('11734548', 'wzzBjOhG79YvnKlcDNvAEgSF', 'yTU1GBFRSapnV2itOKW1rGMtFWDujW3m');
        $image = file_get_contents($file);

        // 如果有可选参数
        $options = array();
        $options["language_type"] = "CHN_ENG";
        $options["detect_direction"] = "true";
        $options["detect_language"] = "true";
        $options["probability"] = "true";

        // 带参数调用通用文字识别, 图片参数为本地图片
        $result = $client->businessCard($image, $options);
        print_r($result);exit;
        // 带参数调用通用文字识别, 图片参数为远程url图片
        // $url = "https//www.x.com/sample.jpg";
        // $client->basicGeneralUrl($url, $options);
    }
}
