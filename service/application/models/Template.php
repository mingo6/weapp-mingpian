<?php
use EasyWeChat\Factory;

class Template extends  CI_Model 
{
    protected $app;
    public function __construct()
    {
        $wechat = $this->config->item('wechat');
        $config = [
            'app_id' => $wechat['appid'],
            'secret' => $wechat['appsecret'],
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => LOG_PATH . 'wechat.log',
            ],
        ];
        $this->app = Factory::miniProgram($config);
    }
    public function send($template_id, $touser, $page, $formId, $data)
    {
    	$d = [];
    	if (isset($data['keyword0'])) {
            foreach ($data as $key => $item) {
                $d[$key] = ['value' => $item];
            }
    	} else {
            for ($i = 1; $i <= count($data); $i++) { 
                $d['keyword' . $i] = ['value' => $data[$i - 1]];
            }
    	}
    	$data = $d;
    	if ($touser) {
   			$query = $this->db->where('id', $touser)->limit(1)->get('wechat_user');
   			$user = $query->row_object();
   			if ($user == false) {
   				return false;
   			}
   			$touser = $user->openid;
    	} else {
   			return false;
    	}

        $result = $this->app->template_message->send([
            'touser' => $touser,
            'template_id' => $template_id,
            'page' => $page,
            'form_id' => $formId,
            'data' => $data,
        ]);
        return $result;
        if ($result->errcode != 0) {
            return false;
        }
        return true;
    }
    public function agreeExchange($touser, $card_id, $name, $formId)
    {
    	$data = [
    		$name,
    		date('Y-m-d H:i:s'),
    		"{$name}同意了你交换名片的申请"
    	];
        return $this->send('YOtmWu6_PYwEix3R3RvDyqmdW7lL6jcmvL4xhfqB4ow', $touser, '/pages/card/info?id=' . $card_id, $formId, $data);
    }
    public function refuseExchange($touser, $name, $formId)
    {
    	$data = [
    		$name,
    		"很遗憾！{$name}没有同意与您交换名片。",
    		date('Y-m-d H:i:s')
    	];
        return $this->send('ER7nzMTX-14ulFVbf6lQjj9rqOtu6u8cEpEqoegYIs4', $touser, '/pages/card/index', $formId, $data);
    }
    public function exchange($touser, $name, $formId)
    {
    	$data = [
    		$name,
    		"您可以点击查看详情，您可以确认接受或拒绝",
    	];
        return $this->send('jgE8Aa5nP5Tju8i7WFBXwSu9D6kthWcDf9qq3OPVQsA', $touser, '/pages/mine/apply/info', $formId, $data);
    }
}
