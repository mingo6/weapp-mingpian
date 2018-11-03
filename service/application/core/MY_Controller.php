<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class MY_Controller extends REST_Controller
{
    public function __construct($type = null)
    {
        parent::__construct();
        switch ($type) {
            case 'api':
                $this->load->library('api_helper', null, 'helper');
                break;
            case 'admin':
                $this->load->library('admin_helper', null, 'helper');
                break;
            case 'app':
                $this->load->library('app_helper', null, 'helper');
                break;
            default:
                break;
        }
    }
}
class REST_Base extends MY_Controller
{
    public $user_token = '';
    public $user_id = 0;
    public $user = null;
    public function __construct($type = null)
    {
        parent::__construct($type);
        $this->user_token = $_SERVER['HTTP_USER_TOKEN'] ?? '';
        $query = $this->db->from('wechat_user')->where('openid', $this->user_token)->limit(1)->get();
        if ($query->num_rows() > 0) {
            $user = $query->row_array();
            $this->user_id = (int) $user['id'];
            $this->user = $user;
        }
    }
    public function send_result($msg = 'ok', $ret = 0, $data = null, $http_code = null)
    {
        $r = compact('ret', 'msg');
        if (!is_null($data)) {
            $r['data'] = $data;
        }
        return $this->set_response($r, $http_code, true);
    }
    public function send_data($data = null, $http_code = null)
    {
        return $this->set_response($data, $http_code, true);
    }
    public function send_success($data = null, $msg = '', $http_code = null)
    {
        // $msg = $msg ?: ErrorMessage::$code['0'];
        return $this->send_result($msg, 0, $data, $http_code);
    }
    public function send_fail($msg, $ret = 1, $data = null)
    {
        // $msg = '';
        return $this->send_result($msg, is_null($ret) ? 1 : $ret, $data);
    }
}
class Base_Controller extends REST_Base
{

    public function __construct()
    {
        parent::__construct();
    }
    public function qiniuUpload($filePath, $path = '', $fileName = '')
    {
        $config = $this->config->item('qiniu');

        $auth = new Qiniu\Auth($config['accessKey'], $config['secretKey']);
        $bucket = $config['bucket'];
        $domain = $config['domain'];
        // 生成上传Token
        $token = $auth->uploadToken($bucket);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if ($path) {
            $path .= '/';
        }
        // 上传到七牛后保存的文件名
        if ($fileName) {
            $key = $path . $fileName;
        } else {
            $key = $path . random_string('md5', 16) . "." . $extension;
        }
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new Qiniu\Storage\UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        $url = $domain . $ret['key'];
        return $url;
    }
    public function updateImage($card, $full = false, $simple = false)
    {
        $id = $card['id'];
        $logo_path = UPLOAD_PATH . $id . '_qrcode_logo.png';
        if (!file_exists($logo_path)) {
            $logo_path = $this->getQrCode($id);
            if (empty($card['qrcode_url'])) {
                $qrcode_url = $this->qiniuUpload($logo_path, 'qrcode');
            }
        }
        $logo = $this->loadImage($logo_path);

        if ($full) {
            list($card_url, $full_url) = $this->makeCardFull($card, $logo);
        }
        if ($simple) {
            $simple_url = $this->makeCardSimple($card, $logo);
        }

        // $simple_url = $this->makeCardSimple($card, $logo_path);
        // $url = $this->qiniuUpload($filePath, 'card');

        // list($card_url, $full_url) = $this->makeCardFull($card, $logo_path);
        $data = compact('qrcode_url', 'simple_url', 'card_url', 'full_url');
        $result = $this->db->where('id', $id)->set($data)->update('card');
        if ($result) {
            return $this->send_success($data);
        } else {
            return $this->send_fail();
        }
    }
    public function makeNewLogo($id, $filePath)
    {
        // 编辑已保存的原头像，保存成圆形（其实不是圆形，改变它的边角为透明）。
        list($w, $h, $type) = getimagesize($filePath);
        $imgObj = $this->loadImage($filePath, $type);
        $w = min($w, $h);
        $o_image = $this->circleImage($imgObj, $w, $w);
        imagedestroy($imgObj);
        // $file_name = $id . "_logo_circle.png";
        // imagepng($imgg, UPLOAD_PATH . $file_name);
        // imagedestroy($imgg);
        $width = 430 * 250 / 480;

        $imgNew = $this->zoomImage($o_image, $width, $w);
        @imagedestroy($o_image);
        // $comp_path = UPLOAD_PATH . $id . "_logo_size.png";
        // imagepng($target_im, $comp_path);
        // 进行拼接。（使用加水印方式把处理过后的头像盖住logo）
        $imgLogo = $this->mergeLogoCode($id, $imgNew, "center");
        @imagedestroy($imgNew);
        return $imgLogo;
        // $qrcode_url = $this->qiniuUpload($filePath, 'qrcode');
        // return $qrcode_url;
    }
    public function loadImage($filePath = '', $type = '')
    {
        if ($type == false) {
            list($width,
                $height,
                $type) = getimagesize($filePath);
        }
        $types = [
            1 => "GIF",
            2 => "JPEG",
            3 => "PNG",
            4 => "SWF",
            5 => "PSD",
            6 => "BMP",
            7 => "TIFF",
            8 => "TIFF",
            9 => "JPC",
            10 => "JP2",
            11 => "JPX",
            12 => "JB2",
            13 => "SWC",
            14 => "IFF",
            15 => "WBMP",
            16 => "XBM",
        ];
        $type = strtolower($types[$type]);
        $fun = "imagecreatefrom" . $type;
        return $fun($filePath);
    }
    public function zoomImage($imgSrc = null, $width, $w)
    {
        $target_im = imagecreatetruecolor($width, $width);
        // 缩小头像（430的小程序码logo为192）
        imagesavealpha($target_im, true);
        $trans_colour = imagecolorallocatealpha($target_im, 0, 0, 0, 127);
        imagefill($target_im, 0, 0, $trans_colour);

        // $o_image = imagecreatefrompng(UPLOAD_PATH . $file_name); //获取上文已保存的修改之后头像的内容
        // $wh = getimagesize(UPLOAD_PATH . $file_name);
        // $w = $wh[0];
        imagecopyresampled($target_im, $imgSrc, 0, 0, 0, 0, $width, $width, $w, $w);
        return $target_im;
    }
    /**
     * [create_pic_watermark 添加图片水印]  头像贴在二维码中间
     * @param  [string] $dest_image [需要添加图片水印的图片名]
     * @param  [string] $watermark  [水印图片名]
     * @param  [string] $locate     [水印位置，center,left_buttom,right_buttom三选一]
     * @return [type]             [description]
     */
    public function mergeLogoCode($id, $img, $locate)
    {
        $watermark = $this->getQrCode($id);
        $dwidth = $dheight = 430 * 250 / 480;
        list($wwidth, $wheight, $type) = getimagesize($watermark);
        // var_dump(compact('wwidth', 'wheight', 'dwidth'));exit;
        $wwidth = $wheight = 480;

        $imgd = $this->loadImage($watermark, $type);
        switch ($locate) {
            case 'center':
                $x = ($wwidth - $dwidth) / 2;
                $y = ($wheight - $dheight) / 2;
                break;
            case 'left_buttom':
                $x = 1;
                $y = ($wheight - $dheight - 2);
                break;
            case 'right_buttom':
                $x = ($wwidth - $dwidth - 1);
                $y = ($wheight - $dheight - 2);
                break;
            default:
                die("未指定水印位置!");
                break;
        }
        imagecopy($imgd, $img, $x, $y, 0, 0, $dwidth, $dwidth);
        imagedestroy($img);
        // imagedestroy($imgd);
        return $imgd;
        // return $f_file_name;
    }
    public function makeCardFull($card, $code_img)
    {
        $id = $card['id'];
        $template_id = $card['template_id'] ?? 1;
        $im = $this->loadImage(APPPATH . 'data' . DS . 'template' . DS . $template_id . '_full.png');
        // $im = imagecreatetruecolor(600, 650);
        $background = imagecolorallocate($im, 0xEE, 0xEE, 0xEE);
        $white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);

        // imagefilledrectangle($im, 0, 0, 600, 360, $background);
        // imagefilledrectangle($im, 0, 360, 600, 650, $white);

        $black = imagecolorallocate($im, 0x00, 0x00, 0x00);
        $gray = imagecolorallocate($im, 0x66, 0x66, 0x66);
        $font = APPPATH . 'data' . DS . 'msyhl.ttc';
        // var_dump($font);exit;
        imagefttext($im, 22, 0, 45, 78, $black, $font, $card['name']);
        imagefttext($im, 12, 0, 45, 114, $black, $font, $card['company']);
        imagefttext($im, 12, 0, 45, 144, $black, $font, $card['position']);

        $list = [];
        if ($card['mobile']) {
            array_unshift($list, 'mobile');
        }
        if ($card['email']) {
            array_unshift($list, 'email');
        }
        if ($card['phone']) {
            array_unshift($list, 'phone');
        }
        if ($card['address']) {
            array_unshift($list, 'address');
        }
        for ($i = 0; $i < count($list); $i++) {
            $type = $list[$i];
            if ($type == 'address') {
                // $len = (mb_strlen($card['address']) + strlen($card['address'])) /2;
                $len = mb_strlen($card['address']);
                $h = ceil($len / 20);
                for ($m = 0; $m < $h; $m++) {
                    imagefttext($im, 12, 0, 65, 310 + $m * 20, $black, $font, mb_substr($card['address'], $m * 20, 20));
                }
            } else {
                imagefttext($im, 12, 0, 65, 310 - $i * 30, $black, $font, $card[$type]);
            }
        }
        // imagefttext($im, 12, 0, 65, 220, $black, $font, $card['mobile']);
        // imagefttext($im, 12, 0, 65, 250, $black, $font, 'shearer2001@163.com');
        // imagefttext($im, 12, 0, 65, 280, $black, $font, '0535-6482971');
        // imagefttext($im, 12, 0, 65, 310, $black, $font, '山东省烟台市福瑞大街200号');
        // $code_img = imagecreatefrompng(UPLOAD_PATH . $id . '_qrcode_logo.png');
        // $code_img = $this->loadImage($logo_img);
        // $code_img = imagecreatefrompng($logo_img);
        // $fp_img = imagecreatefrompng(APPPATH . 'data' . DS . 'fingerprint.png');
        $new_code_img = imagecreatetruecolor(150, 150);
        // imagepng($new_code_img, UPLOAD_PATH . '1.png');
        // imagepng($code_img, UPLOAD_PATH . '2.png');
        imagecopyresampled($new_code_img, $code_img, 0, 0, 0, 0, 150, 150, 480, 480);
        // imagepng($new_code_img, UPLOAD_PATH . $id . '_card_1.png');

        imagecopy($im, $new_code_img, 100, 430, 0, 0, 150, 150);
        if ($card['avatar']) {
            $fileName = pathinfo($card['avatar'], PATHINFO_BASENAME);
            $filePath = UPLOAD_PATH . $fileName;
            if (!file_exists($filePath)) {
                $fileName = 'a_' . md5($card['avatar']) . '.png';
                $filePath = UPLOAD_PATH . $fileName;
                if (!file_exists($filePath)) {

                    $client = new GuzzleHttp\Client(['verify' => false]);
                    $response = $client->get($card['avatar'], ['save_to' => $filePath]);
                    if ($response->getStatusCode() == 200) {
                    }
                }
            }

            if (file_exists($filePath)) {
                list($w, $h, $type) = getimagesize($filePath);
                $w = max($w, $h);

                $avatar = $this->loadImage($filePath, $type);
                $imgAvatar = $this->zoomImage($avatar, 108, $w);
                imagecopy($im, $imgAvatar, 462, 30, 0, 0, 108, 108);
            } else {

            }
        }
        // imagecopy($im, $fp_img, 360, 450, 0, 0, 128, 128);
        // imagefttext($im, 12, 0, 150, 610, $black, $font, '长按识别二维码 使用“名片口袋”保存名片信息');
        // $f_file_name = UPLOAD_PATH . $id . '_card_simple.png';
        // header('Content-Type: image/png');
        $filePath = UPLOAD_PATH . $id . '_card_full.png';
        imagepng($im, $filePath);
        $full_url = $this->qiniuUpload($filePath, 'card');
        // imagedestroy($code_img);
        imagedestroy($new_code_img);
        // imagedestroy($fp_img);

        $target_image = imagecreatetruecolor(600, 360);
        imagecopy($target_image, $im, 0, 0, 0, 0, 600, 360);
        imagedestroy($im);
        $filePath = UPLOAD_PATH . $id . '_card.png';
        imagepng($target_image, $filePath);
        $url = $this->qiniuUpload($filePath, 'card');
        imagedestroy($target_image);
        // print_r($filePath);exit;
        return [$url, $full_url];
    }
    public function makeCardSimple($card, $code_img)
    {
        $id = $card['id'];
        // $template_id = $card['template_id'] ?: 1;
        $im = $this->loadImage(APPPATH . 'data' . DS . 'template' . DS . 'simple.png');
        // $im = imagecreatetruecolor(600, 800);
        // $white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
        $black = imagecolorallocate($im, 0x00, 0x00, 0x00);
        $gray = imagecolorallocate($im, 0x66, 0x66, 0x66);
        // Make the background red
        // function imagefilledrectangle ($image, $x1, $y1, $x2, $y2, $color) {}
        // imagefilledrectangle($im, 0, 0, 600, 800, $white);
        $font = APPPATH . 'data' . DS . 'msyhl.ttc';
        // var_dump($font);exit;
        imagefttext($im, 30, 0, 60, 80, $black, $font, $card['name']);
        imagefttext($im, 20, 0, 60, 124, $gray, $font, $card['company']);
        imagefttext($im, 20, 0, 60, 164, $gray, $font, $card['position']);
        // imagefttext($im, 16, 0, 60, 730, $gray, $font, '使用微信识别二维码，收名片，获取我的联系方式');
        // $code_img = imagecreatefrompng($logo_img);
        // $code_img = $this->loadImage($logo_img);
        imagecopy($im, $code_img, 60, 200, 0, 0, 480, 480);
        $filePath = UPLOAD_PATH . $id . '_card_simple.png';
        imagepng($im, $filePath);
        imagedestroy($im);
        // imagedestroy($code_img);
        // return $im;
        $url = $this->qiniuUpload($filePath, 'card');
        return $url;
    }
    /**
     * [circleImage 编辑图片为圆形]  剪切头像为圆形
     * @param  [string] $imgpath [头像保存之后的图片名]
     */
    public function circleImage($src_img, $w)
    {
        // $ext = pathinfo($imgpath);
        // $src_img = null;
        // switch ($ext['extension']) {
        //     case 'jpg':
        //         $src_img = imagecreatefromjpeg($imgpath);
        //         break;
        //     case 'png':
        //         $src_img = imagecreatefrompng($imgpath);
        //         break;
        // }
        // $wh = getimagesize($imgpath);
        // $w = $wh[0];
        // $h = $wh[1];
        // $w = min($w, $h);
        $h = $w;
        $img = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
        imagefilledrectangle($img, 0, 0, $w, $w, $white);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        return $img;
    }
}
