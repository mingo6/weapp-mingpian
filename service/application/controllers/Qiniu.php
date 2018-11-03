<?php

use Restserver\Libraries\REST_Controller;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Qiniu extends REST_Base  {

    function __construct()
    {
        parent::__construct();
    }
    public function uptoken_post()
    {
        $config = $this->config->item('qiniu');
        $auth = new Auth($config['accessKey'], $config['secretKey']);
        $bucket = 'mingpian';
        // 生成上传Token
        $token = $auth->uploadToken($bucket, null , 3600, '');
        return $this->send_data(['uptoken' => $token]);
    }
    public function uptoken_get()
    {
        $config = $this->config->item('qiniu');
        $auth = new Auth($config['accessKey'], $config['secretKey']);
        $bucket = 'mingpian';
		// 生成上传Token
		$token = $auth->uploadToken($bucket);
        return $this->send_data(['uptoken' => $token]);
    }

}
