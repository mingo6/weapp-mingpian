<?php
/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code)
{
    static $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded',
    );
    if (isset($_status[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:' . $code . ' ' . $_status[$code]);
    }
}

/**
 * GET 请求
 * @param string $url
 */
function http_get($url)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== false) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($oCurl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, 1);

    $sContent = curl_exec($oCurl);
    $aStatus  = curl_getinfo($oCurl);
    curl_close($oCurl);

    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/**
 * POST 请求
 * @param string $url
 * @param array $param
 * @param boolean $post_file 是否文件上传
 * @return string content
 */
function http_post($url, $param, $post_file = false)
{

    $oCurl = curl_init();

    if (stripos($url, "https://") !== false) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (is_string($param) || $post_file) {
        $strPOST = $param;
        if (is_json($param)) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        }
    } else {
        $strPOST = http_build_query($param);
    }

    curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    // curl_setopt($oCurl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, 1);

    $sContent = curl_exec($oCurl);
    $aStatus  = curl_getinfo($oCurl);
    curl_close($oCurl);
    dump($aStatus);
        return $sContent;
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}
function returnResult($msg = '', $ret = 0, $data = null)
{
    $ret = [
        'ret' => $ret,
        'msg' => $msg,
    ];
    if (!is_null($data)) {
        $ret['data'] = $data;
    }
    return $ret;
}
function returnFail($msg = 'fail', $ret = 1, $data = null)
{
    return returnResult($msg, $ret, $data);
}
function returnSuccess($msg = '', $data = null)
{
    return returnResult($msg, 0, $data);
}

function returnData($data = null, $msg = '成功')
{
    return returnResult($msg, 0, $data);
}
function sendResult($message = 'ok', $ret = 0, $data = null)
{
    ob_clean();
    $r = array(
        'ret' => $ret,
        'msg' => $message,
    );
    if (!is_null($data)) {
        $r['data'] = $data;
    }
    ajaxHeader();
    exit(json_encode($r, JSON_UNESCAPED_UNICODE));
}

function sendData($data = null)
{
    exit(json_encode($data, JSON_UNESCAPED_UNICODE));
}

function sendSuccess($data = null, $msg = 'success')
{
    sendResult($msg, 0, $data);
}

//参数 [$errorMsg, $errorCode]
function sendFail($msg = 'fail', $ret = null, $data = null)
{
    sendResult($msg, is_null($ret) ? 1 : $ret, $data);
}

function ajaxHeader()
{
    $origin  = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $domains = [
        'xiaoxiancai.com.cn',
        'jiuyebangs.com',
        'jobspace.com',
        // '.',
    ];
    $root = strtolower(getRootDomain($origin));
    // if (in_array($origin, $hosts)) {
    if (in_array($root, $domains)) {
        header("Access-Control-Allow-Origin:{$origin}");
    } else {
        header("Access-Control-Allow-Origin:*");
    }
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH");
    header('Access-Control-Allow-Headers: Content-Type,USERTYPE,USERTOKEN,SESSION-KEY');
    header('Content-Type:application/json;charset=utf-8');
}
// function cookie()
// {
//     $args  = func_get_args();
//     $name  = $args[0] ?? '';
//     $value = $args[1] ?? '';
//     if (func_num_args() == 1) {
//         return $_COOKIE[$name] ?? '';
//     }
//     if (func_num_args() == 2) {
//         $config = \Phalcon\DI::getDefault()->getConfig();
//         $a      = setcookie($name, $value, $config->application->user_expire_in, '/', MASTER_DOMAIN);
//     }
// }

/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $option cookie参数
 * @return mixed
 */
function cookie($name = '', $value = null, $option = null)
{
    // 默认设置
    $config = [
        'prefix'   => getenv('COOKIE_PREFIX') ?: '', // cookie 名称前缀
        'expire'   => getenv('COOKIE_EXPIRE') ?: 86400, // cookie 保存时间
        'path'     => getenv('COOKIE_PATH') ?: '/', // cookie 保存路径
        'domain'   => getenv('COOKIE_DOMAIN') ?: MASTER_DOMAIN, // cookie 有效域名
        'secure'   => getenv('COOKIE_SECURE'), //  cookie 启用安全传输
        'httponly' => getenv('COOKIE_HTTPONLY'), // httponly设置
    ];

    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option)) {
            $option = ['expire' => $option];
        } elseif (is_string($option)) {
            parse_str($option, $option);
        }
        $config = array_merge($config, array_change_key_case($option));
    }
    if ($config['httponly']) {
        ini_set("session.cookie_httponly", 1);
    }
    $name = $config['prefix'] . str_replace('.', '_', $name);

    if ('' === $value) {
        setcookie($name, '', time() - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
        unset($_COOKIE[$name]); // 删除指定cookie
    } else {
        if (is_null($value)) {
            if (isset($_COOKIE[$name])) {
                $value = $_COOKIE[$name];
                if (0 === strpos($value, 'phalcon:')) {
                    $value = substr($value, 8);
                    return array_map('urldecode', json_decode(MAGIC_QUOTES_GPC ? stripslashes($value) : $value, true));
                } else {
                    return $value;
                }
            } else {
                return null;
            }
        } else {
            // 设置cookie
            if (is_array($value)) {
                $value = 'phalcon:' . json_encode(array_map('urlencode', $value));
            }
            $expire         = empty($config['expire']) ? 0 : time() + intval($config['expire']);
            $result         = setcookie($name, $value, $expire, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
            $_COOKIE[$name] = $value;
        }
    }
    return null;
}
if (!function_exists('apache_request_headers')) {
    function apache_request_headers()
    {
        $arh     = array();
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key    = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);
                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }

                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return ($arh);
    }
}
