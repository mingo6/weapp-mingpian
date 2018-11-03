<?php
// namespace Jobspace\Library\Tencent;

/**
 *
 */
class TencentMap
{
    const API_URL_PREFIX      = 'http://apis.map.qq.com/ws';
    const WS_GEOCODER_ADDRESS = '/geocoder/v1/?';
    const WS_COORD_TRANSLATE  = '/coord/v1/translate?';
    const WS_IP               = '/location/v1/ip?';
    static $_params           = [];
    static $errCode           = '';
    static $errMsg            = '';
    public static function options($options = [])
    {
        $params = ['key' => $options['key'] ?? ''];
        if (!empty($options['sk'])) {
            $params['sn'] = $options['sk'];
        }
        self::$_params = $params;
    }
    protected static function getBaseQuery()
    {
        return self::$_params;
    }
    public static function build_url($apiurl, $query)
    {
        $url    = self::API_URL_PREFIX . $apiurl;
        $params = [];
        foreach ($query as $key => $value) {
            $params[] = $key . '=' . $value;
        }
        return $url . implode('&', $params);
    }
    public static function getAddress($lat, $lng, $coord_type = 5, $get_poi = 0, $poi_options = '', $output = 'json', $callback = '')
    {
        $basequery = self::getBaseQuery();
        // Logger::info($basequery);
        $basequery['location']   = $lat . ',' . $lng;
        $basequery['coord_type'] = $coord_type;
        $basequery['get_poi']    = $get_poi;
        $url                     = self::API_URL_PREFIX . self::WS_GEOCODER_ADDRESS . http_build_query($basequery);
        $result                  = http_get($url);

        if ($result) {
            $json = json_decode($result, true);
            if (!$json || isset($json['status']) && $json['status'] != 0) {
                self::$errCode = $json['status'];
                self::$errMsg  = $json['message'];
                // App::writeLog(self::$errMsg, 'error');
                return false;
            }
            return $json['result'];
        }
        return false;
    }
    public static function getLocation($address, $output = 'json', $callback = '')
    {
        $basequery            = self::getBaseQuery();
        $basequery['address'] = $address;
        $url                  = self::API_URL_PREFIX . self::WS_GEOCODER_ADDRESS . http_build_query($basequery);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || isset($json['status']) && $json['status'] != 0) {
                self::$errCode = $json['status'];
                self::$errMsg  = $json['message'];
                // App::writeLog(self::$errMsg, 'error');
                return false;
            }
            return $json['result'];
        }
        return false;
    }
    /**
     * 实现从其它图商坐标系或标准gps坐标系，批量转换到腾讯地图坐标系。
     * @return [type] [description]
     */
    public static function translate($lat, $lng, $type = 5, $output = 'json', $callback = '')
    {
        $basequery              = self::getBaseQuery();
        $basequery['locations'] = $lat . ',' . $lng;
        $basequery['type']      = $type;
        $url                    = self::API_URL_PREFIX . self::WS_COORD_TRANSLATE . http_build_query($basequery);
        $result                 = http_get($url);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || isset($json['status']) && $json['status'] != 0) {
                self::$errCode = $json['status'];
                self::$errMsg  = $json['message'];
                // App::writeLog(self::$errMsg, 'error');
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 通过终端设备IP地址获取其当前所在地理位置，精确到市级，常用于显示当地城市天气预报、初始化用户城市等非精确定位场景。
     * @return [type] [description]
     */
    public static function ip2address($ip = '')
    {
        $basequery = self::getBaseQuery();
        if ($ip) {
            $basequery['ip'] = $ip;
        }

        $url    = self::API_URL_PREFIX . self::WS_IP . http_build_query($basequery);
        $result = http_get($url);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || isset($json['status']) && $json['status'] != 0) {
                self::$errCode = $json['status'];
                self::$errMsg  = $json['message'];
                // App::writeLog(self::$errMsg, 'error');
                return false;
            }
            return $json['result'];
        }
        return false;
    }
}
