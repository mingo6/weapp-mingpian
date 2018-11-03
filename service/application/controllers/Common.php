<?php

class Common extends REST_Base
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 经纬度转地址
     * @return [type] [description]
     */
    public function geo2address_get()
    {
    	$this->load->library('TencentMap');
    	$this->load->config('tencent');
    	$config = $this->config->item('map');
        $lat    = $this->get('lat');
        $lng    = $this->get('lng');
        TencentMap::options($config);
        $result = TencentMap::getAddress($lat, $lng);
        $data = $result['ad_info'];
        $data['town'] = $result['address_reference']['town']['title'] ?? '';
        $data['address'] = $result['address'] ?? '';
        unset($data['location']);
        return $this->send_success($data);
        $this->send_success($result);
    }
    public function ip2address_get()
    {
    	$this->load->library('TencentMap');
    	$this->load->config('tencent');
    	$config = $this->config->item('map');
        TencentMap::options($config);
        $result = TencentMap::ip2address();
        // return $this->send_success($result);
        $data = $result['ad_info'];
        $data['ip'] = $result['ip'] ?? '';
        $data['latitude'] = $result['location']['lat'] ?? '';
        $data['longitude'] = $result['location']['longitude'] ?? '';
        return $this->send_success($data);
    }
}