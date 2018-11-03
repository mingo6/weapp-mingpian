<?php

use Restserver\Libraries\REST_Controller;
use EasyWeChat\Factory;

class Wechat extends REST_Base  {

    function __construct()
    {
        parent::__construct();
    }
    public function openid_get()
    {
        $wechat = $this->config->item('wechat');
        $this->send_data($wechat);
    }

    public function openid_post()
    {
        $code = $this->post('code');
        $wechat = $this->config->item('wechat');
        $config = [
            'app_id' => $wechat['appid'],
            'secret' => $wechat['appsecret'],
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => LOG_PATH . 'wechat.log',
            ],
        ];

        $app = Factory::miniProgram($config);
        $session = $app->auth->session($code);
        if ($session && !empty($session['openid'])) {
            $query  = $this->db->from('wechat_user')->where('openid', $session['openid'])->limit(1)->get();
            if ($query->num_rows() < 1) {
                $data = $session;
                $data['appid'] = $wechat['appid'];
                $this->db->insert('wechat_user', $data);
                $session['user_id'] = $this->db->insert_id();
            } else {
                $user = $query->row_array();
                $session['user_id'] = (int) $user['id'];
            }
        }
        $this->send_success($session);
    }

    public function userinfo_post()
    {
        // $avatarUrl = $this->post('avatarUrl');
        // $city = $this->post('city');
        // $country = $this->post('country');
        // $gender = $this->post('gender');
        // $language = $this->post('language');
        // $nickName = $this->post('nickName');
        // $province = $this->post('province');
        $data = $this->post();
        unset($data['r']);
        if ($this->user_token == false) {
            return $this->send_fail();
        }
        if ($this->user_id) {
            // $data['id'] = $this->user_id;
            // $this->db->replace('wechat_user', $data);
            $this->db->where('id', $this->user_id);
            $this->db->update('wechat_user', $data);
        } else {
            $this->db->set($data);
            $this->db->insert('wechat_user');
            $this->user_id = $this->db->insert_id();
        }
        return $this->send_success($this->user_id);
    }
    public function phone_post()
    {
        $wechat = $this->config->item('wechat');
        $config = [
            'app_id' => $wechat['appid'],
            'secret' => $wechat['appsecret'],
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => LOG_PATH.'wechat.log',
            ],
        ];
        $app = Factory::miniProgram($config);
        $session = $this->post('session');
        $iv = $this->post('iv');
        $encryptData = $this->post('encryptedData');
        $decryptedData = $app->encryptor->decryptData($session, $iv, $encryptData);
        return $this->send_success($decryptedData);
    }
}
