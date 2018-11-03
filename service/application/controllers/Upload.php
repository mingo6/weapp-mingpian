<?php

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Upload extends REST_Base
{

    public function __construct()
    {
        parent::__construct();
    }
    public function avatar_post()
    {
        $msg = '';
        $config['upload_path'] = UPLOAD_PATH;
        $config['allowed_types'] = 'png|gif|jpg';
        $config['max_size'] = '1024';
        $config['max_width'] = 1024;
        $config['max_height'] = 768;
        $config['file_name'] = 'a_' . random_string('md5', 16);
        $config['overwrite'] = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            return $this->send_fail($this->upload->display_errors());
        }
        $up_data = $this->upload->data();

        $config = $this->config->item('qiniu');
        $auth = new Auth($config['accessKey'], $config['secretKey']);
        $bucket = $config['bucket'];
        $domain = $config['domain'];
        // 生成上传Token
        $token = $auth->uploadToken($bucket);
        $extension = pathinfo($up_data['full_path'], PATHINFO_EXTENSION);
        // 上传到七牛后保存的文件名
        $key = 'avatar/' . $config['file_name'] . "." . $extension;
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $up_data['full_path']);
        if ($err == false) {
            $url = $domain . $ret['key'];
            return $this->send_success($url);
        } else {
            return $this->send_fail($err);
        }
    }
}
