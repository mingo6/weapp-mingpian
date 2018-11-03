<?php

use EasyWeChat\Factory;

class Card extends Base_Controller
{

    public function __construct()
    {
        parent::__construct();
    }
    public function card_get()
    {
        $id = $this->get('id');
        if (strlen($id)) {
            $this->db->from('card');
            $this->db->where('card.status', 1);
            if ($id) {
                $this->db->where('card.id', $id);
            } else {
                $this->db->where('card.user_id', $this->user_id);
            }

            $this->db->join('card_part', 'card.id = card_part.card_id AND card_part.handler=' . $this->user_id, 'left');
            $this->db->select('card.*,card_part.viewed,card_part.liked,card_part.collected,card_part.remark')->limit(1);
            // $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->row_array() ?: false;
            $this->viewCard($result);
        } else {
            $query = $this->db->from('card')->where('status', 1)->where('user_id', $this->user_id)->get();
            $result = $query->result_array() ?: [];
        }
        return $this->send_success($result);
    }
    public function viewCard($card = false)
    {
        if ($card && $this->user_id && $this->user_id !== $card['user_id']) {
            $query = $this->db->from('card_part')->where('handler', $this->user_id)->where('card_id', $card['id'])->limit(1)->get();
            $part = $query->row_array();
            if ($part) {
                $result = $this->db->where('id', $part['id'])->set('viewed', 'viewed+1', false)->update('card_part');
            } else {
                $part = [
                    'owner' => $card['user_id'],
                    'handler' => $this->user_id,
                    'card_id' => $card['id'],
                    'viewed' => 1,
                    'update_time' => time(),
                ];
                $this->db->set($part);
                $this->db->insert('card_part');
                $result = $this->db->insert_id();
            }
        } else {
            $result = false;
        }
        if ($card) {
            $result = $this->db->where('id', $card['id'])->set('viewes', 'viewes+1', false)->update('card');
            $part = [
                'viewer' => $this->user_id,
                'card_id' => $card['id'],
                'view_time' => time(),
            ];
            $this->db->set($part);
            $this->db->insert('card_view_log');
        }
        return $result;
    }
    public function card_post()
    {
        $data = $this->post();
        unset($data['r']);
        if ($this->user_token == false or $this->user_id == false) {
            return $this->send_fail();
        }
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        $name = $pinyin->name($data['name']);
        $name = join('', $name);
        $data['pinyin'] = $name;
        $data['update_time'] = time();
        $data['user_id'] = $this->user_id;
        $data['status'] = 1;
        $this->db->set($data);
        $this->db->insert('card');
        $id = $this->db->insert_id();
        $data['id'] = $id;
        return $this->updateImage($data, true, true);
        // return $this->send_success($id);
    }
    public function card_put()
    {
        $data = $this->put();
        unset($data['r']);
        if ($this->user_token == false or $this->user_id == false) {
            return $this->send_fail();
        }
        $query = $this->db->from('card')->where('id', $data['id'])->limit(1)->get();
        $card = $query->row_array();
        if ($card == false) {
            return $this->send_fail($id);
        }
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        $name = $pinyin->name($data['name']);
        $name = join('', $name);
        $data['pinyin'] = $name;
        $data['update_time'] = time();
        $data['user_id'] = $this->user_id;
        $data['status'] = 1;
        $data = array_merge($card, $data);
        $result = $this->db->where('id', $data['id'])->set($data)->update('card');
        if ($result) {
            $full = $simple = false;
            if ($card['name'] != $data['name'] || $card['company'] != $data['company'] || $card['position'] != $data['position']) {
                $full = $simple = true;
            }
            if ($card['mobile'] != $data['mobile'] || $card['email'] != $data['email'] || $card['phone'] != $data['phone'] || $card['address'] != $data['address']) {
                $simple = true;
            }
            return $this->updateImage($data, $full, $simple);
        } else {
            return $this->send_fail();
        }
    }
    public function card_delete()
    {
        $data = $this->delete();
        // $data = json_decode($data, true);
        // return $this->send_success($data);
        unset($data['r']);
        $data['status'] = 0;
        $result = $this->db->where('id', $data['id'])->set($data)->update('card');
        return $this->send_success($result);

    }
    public function part_get()
    {
        $types = $this->get('types');
        $uid = $this->user_id;
        $this->db->from('card_part')->where('owner', $uid);
        $this->db->join('card', 'card.user_id = card_part.handler');
        switch ($types) {
            case 'browse':
                $this->db->where('browsed', 1);
                break;
            case 'like':
                $this->db->where('liked', 1);
                break;
            case 'collect':
                $this->db->where('collected', 1);
                break;
            default:
                # code...
                break;
        }
        $query = $this->db->get();
        $items = $query->result_array();
        return $this->send_success($items);
    }
    public function part_put()
    {
        $id = (int) $this->put('id');
        $data = $this->put();
        unset($data['r']);
        $uid = $this->user_id;
        $result = $this->db->where('card_id', $id)->where('handler', $uid)->set($data)->update('card_part');
        if ($result) {
            return $this->send_success();
        } else {
            return $this->send_fail();
        }
    }
    public function control_post()
    {
        $id = (int) $this->post('id');
        $type = $this->post('type');
        $uid = $this->user_id;
        $query = $this->db->from('card')->where('id', $id)->limit(1)->get();
        $result = false;
        if ($query->num_rows() > 0) {
            $card = $query->row_array();
            $query = $this->db->from('card_part')->where('card_id', $id)->where('handler', $uid)->limit(1)->get();
            if ($query->num_rows() == 0) {
                $state = 1;
                $part = [
                    'owner' => $card['user_id'],
                    'handler' => $uid,
                    'card_id' => $id,
                    'viewed' => 1,
                    'update_time' => time(),
                ];
                if ($type == 'like') {
                    $part['liked'] = $state;
                }
                if ($type == 'collect') {
                    $part['collected'] = $state;
                }
                $this->db->set($part);
                $this->db->insert('card_part');
                $result = $this->db->insert_id();
                // $state = 1;
            } else {
                $part = $query->row_array();
                $state = $part[$type] ? 0 : 1;
                $this->db->set($type, $state);
                $this->db->where('card_id', $id)->where('handler', $uid);
                $result = $this->db->update('card_part');
            }
            $field = '';
            if ($type == 'like') {
                $field = 'likes';
            }
            if ($type == 'collect') {
                $field = 'collectes';
            }
            if ($field) {
                if ($state) {
                    $result = $this->db->where('id', $card['id'])->set($field, "{$field}+1", false)->update('card');
                } else {
                    $result = $this->db->where('id', $card['id'])->set($field, "{$field}-1", false)->update('card');
                }
            }
            // $this->viewCard($card);
        }
        if ($result) {
            return $this->send_success();
        } else {
            return $this->send_fail();
        }

    }

    // public function view_post()
    // {
    //     $id = (int) $this->post('id');
    //     $uid = $this->user_id;
    //     $query = $this->db->from('card')->where('id', $id)->limit(1)->get();
    //     if ($query->num_rows() > 0) {
    //         $card = $query->row_array();
    //         $query = $this->db->from('card_part')->where('card_id', $id)->where('handler', $uid)->limit(1)->get();
    //         if ($query->num_rows() == 0) {
    //             $part = [
    //                 'owner' => $card['user_id'],
    //                 'handler' => $uid,
    //                 'card_id' => $id,
    //                 'viewed' => 1,
    //                 'update_time' => time(),

    //             ];
    //             $this->db->set($part);
    //             $this->db->insert('card_part');
    //             $id = $this->db->insert_id();
    //         } else {
    //             $part = $query->row_array();
    //             $card['collected'] = (int) $part['collected'];
    //             $card['liked'] = (int) $part['liked'];
    //             $card['viewed'] = (int) $part['viewed'];
    //         }

    //     } else {
    //         $card = [];
    //     }
    //     return $this->send_success($card);
    // }
    public function collect_post()
    {
        $id = (int) $this->post('id');
        $uid = $this->user_id;
        $query = $this->db->from('card')->where('id', $id)->limit(1)->get();
        $msg = '';
        if ($query->num_rows() > 0) {
            $card = $query->row_array();
            $query = $this->db->from('card_part')->where('card_id', $id)->where('handler', $uid)->limit(1)->get();
            if ($query->num_rows() > 0) {
                $part = $query->row_array();
                if ($part['collected'] == 1) {
                    $this->db->set('collected', 0);
                } else {
                    $this->db->set('collected', 1);
                }
                $this->db->where('card_id', $id)->where('handler', $uid);
                $this->db->update('card_part');
                return $this->send_success();
            } else {
                $msg = 's';
            }
        } else {
            $msg = 's2';
        }
        return $this->send_fail($msg);
    }
    public function like_post()
    {
        $id = (int) $this->post('id');
        $uid = $this->user_id;
        $query = $this->db->from('card')->where('id', $id)->limit(1)->get();
        $msg = '';
        if ($query->num_rows() > 0) {
            $card = $query->row_array();
            $query = $this->db->from('card_part')->where('card_id', $id)->where('handler', $uid)->limit(1)->get();
            if ($query->num_rows() > 0) {
                $part = $query->row_array();
                if ($part['liked'] == 1) {
                    $this->db->set('liked', 0);
                } else {
                    $this->db->set('liked', 1);
                }
                $this->db->where('card_id', $id)->where('handler', $uid);
                $this->db->update('card_part');
                return $this->send_success();
            } else {
                $msg = 's';
            }
        } else {
            $msg = 's2';
        }
        return $this->send_fail($msg);
    }
    public function upload_post()
    {
        $id = $this->post('id');
        $query = $this->db->from('card')->where('id', $id)->limit(1)->get();
        $msg = '';
        if ($query->num_rows() == 0) {
            $this->send_fail('该名片可能已经删除了，请试试重新进入小程序');
        }
        $card = $query->row_array();
        $timestamp = $card['update_time'];
        $config['upload_path'] = UPLOAD_PATH;
        $config['allowed_types'] = 'png|gif|jpg';
        $config['max_size'] = '1024';
        $config['max_width'] = 1024;
        $config['max_height'] = 768;
        $config['file_name'] = "{$id}_logo";
        $config['overwrite'] = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            return $this->send_fail($this->upload->display_errors());
        }
        $up_data = $this->upload->data();
        // return $this->send_success($up_data);
        $imgLogo = $this->makeNewLogo($id, $up_data['full_path']);
        $fileLogo = UPLOAD_PATH . "{$id}_qrcode_logo.png";
        imagepng($imgLogo, $fileLogo); //保存
        $qrcode_url = $this->qiniuUpload($fileLogo, 'qrcode');
        // return $qrcode_url;
        // $filePath =  $this->makeNewPic($id, $up_data['full_path']);
        // $logo_path = UPLOAD_PATH . $id . "_logo_size.png";

        $simple_url = $this->makeCardSimple($card, $imgLogo);

        list($card_url, $full_url) = $this->makeCardFull($card, $imgLogo);
        $data = compact('qrcode_url', 'simple_url', 'card_url', 'full_url');
        $result = $this->db->where('id', $id)->set($data)->update('card');
        if ($result) {
            return $this->send_success($data);
        } else {
            return $this->send_fail();
        }
    }

    public function qrcode_get()
    {
        $id = $this->get('id');
        $this->saveQrcode($id);
    }
    public function getQrCode($id)
    {
        $fileName = UPLOAD_PATH . $id . '_appcode.png';
        if (file_exists($fileName)) {
            return $fileName;
        }
        $this->saveQrcode($id);
        return $fileName;
    }
    public function saveQrcode($id)
    {
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
        $response = $app->app_code->getUnlimit("id={$id}", ['width' => 480]);
        $filename = $response->saveAs(UPLOAD_PATH, $id . '_appcode.png');
        return $filename;
    }

    public function head_get()
    {
        $id = 6;
        $this->makeCardFull($id);
    }

    public function mine_get()
    {
        $query = $this->db->from('card')->where('status', 1)->where('user_id', $this->user_id)->get();
        $result = $query->result_array();
        return $this->send_success($result);
    }
    public function box_get()
    {
        $uid = $this->user_id;
        $this->db->from('card')->where('card.status', 1);
        $this->db->join('card_part', 'card.id = card_part.card_id AND card_part.handler=' . $this->user_id, 'left');

        $this->db->where('card_part.collected', 1);
        $query = $this->db->select('card.id,card.pinyin,card.name,card.mobile,card.valid,card.company,card.position,card.email,card.phone,card.address,card.weixin,card.intro,card.user_id,card.type,card.card_url,card_part.viewed,card_part.liked,card_part.collected,card_part.remark')->order_by('pinyin')->get();
        $result = $query->result_array();
        $list = [];
        foreach ($result as $item) {
            $pinyin = $item['pinyin'];
            $first = strtoupper(substr($pinyin, 0, 1));
            $list[$first][] = $item;
        }
        $data = [];
        foreach ($list as $k => $item) {
            $data[] = [
                'firstchar' => $k,
                'child' => $item,
            ];
        }
        return $this->send_success($data);
    }
    /**
     * 名片市场
     */
    public function all_get()
    {
        $uid = $this->user_id;
        $page = (int) $this->get('page') ?: 1;
        $limit = (int) $this->get('limit');
        $offset = ($page - 1) * $limit;
        $longitude = $this->get('lng');
        $latitude = $this->get('lat');
        $this->db->from('card')->where('card.status', 1)->join('card_part', 'card.id = card_part.card_id AND card_part.handler=' . $this->user_id, 'left')->select('card.*,card_part.viewed,card_part.liked,card_part.collected')->limit($limit, $offset);
        if ($key = $this->get('key')) {
            $this->db->where("(name like '%{$key}%' OR mobile like '%{$key}%' OR position like '%{$key}%' OR company like '%{$key}%' OR phone like '%{$key}%' OR email like '%{$key}%')");
        }
        $this->db->where('user_id !=', $uid);
        $query = $this->db->get();
        // $sql = $this->db->get_compiled_select();
        // return $this->send_success($sql);
        $result = $query->result_array();
        $this->load->library('Gps');
        foreach ($result as &$item) {
            if ($latitude && $longitude && $item['latitude'] && $item['longitude']) {
                $item['distance'] = Gps::getDistance($item['latitude'], $item['longitude'], $latitude, $longitude);
            }
            $item['viewed'] = (int) $item['viewed'];
            $item['liked'] = (int) $item['liked'];
            $item['collected'] = (int) $item['collected'];
        }
        return $this->send_success($result);
        $dis = Gps::getDistance($location['latitude'], $location['longitude'], $rloc['latitude'], $rloc['longitude']);
    }
    /**
     * 获取自己的名片
     */
    public function edit_get()
    {
        $this->db->from('card');
        $this->db->where('card.status', 1);

        $this->db->where('card.user_id', $this->user_id);

        // $this->db->join('card_part', 'card.id = card_part.card_id AND card_part.handler=' . $this->user_id, 'left');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array() ?: false;

        return $this->send_success($result);
    }
    /**
     * 修改自己的名片
     */
    public function edit_post()
    {
        $data = $this->post();
        unset($data['r']);
        unset($data['imgIndex']);
        if ($this->user_token == false or $this->user_id == false) {
            return $this->send_fail();
        }
        if ($_FILES) {
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
            $fileName = pathinfo($up_data['full_path'], PATHINFO_BASENAME);
            $data['avatar'] = $this->qiniuUpload($up_data['full_path'], 'avatar', $fileName);
        }
        $id = $data['id'] ?? 0;
        unset($data['id']);
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        $name = $pinyin->name($data['name']);
        $name = join('', $name);
        $data['pinyin'] = $name;
        $data['update_time'] = time();
        $data['user_id'] = $this->user_id;
        $data['status'] = 1;

        $query = $this->db->from('card')->where('id', $id)->limit(1)->get();
        if ($query->num_rows() == 0) {
            $this->db->set($data);
            $this->db->insert('card');
            $result = $this->db->insert_id();
            // $state = 1;
        } else {
            $card = $query->row_array();
            $this->db->set($data);
            $this->db->where('id', $id);
            // $data['id'] = $id;
            $result = $this->db->update('card');
        }
        if ($result) {
            if ($id == 0) {
                $data['id'] = $result;
                return $this->updateImage($data, true, true);
            } else {
                $data['id'] = $id;
                $full = $simple = false;
                if ($card['name'] != $data['name'] || $card['company'] != $data['company'] || $card['position'] != $data['position']) {
                    $full = $simple = true;
                }
                if ($card['mobile'] != $data['mobile'] || $card['email'] != $data['email'] || $card['phone'] != $data['phone'] || $card['address'] != $data['address']) {
                    $simple = true;
                }
                return $this->updateImage($data, $full, $simple);
            }
        } else {
            return $this->send_fail();
        }
        // return $this->send_success($result);
    }
    /**
     * 交换名片
     */
    public function exchange_post()
    {
        $id = $this->post('id');
        $type = $this->post('type');
        if ($this->user_token == false or $this->user_id == false) {
            return $this->send_fail();
        }
        $query = $this->db->from('card_apply_log')->where('card_id', $id)->where('requester', $this->user_id)->where('apply_type', $type)->order_by('id DESC')->limit(1)->get();
        $result = $query->row_array();
        if ($result) {
            if ($result['reply_state'] > 0) {
                if ($result['reply_state'] == 1) {
                    return $this->send_success();
                }
            } else {
                return $this->send_fail("您已经提交过名片交换申请，请等待审核，无需重复提交");
            }
        }

        $data = [
            'card_id' => $id,
            'requester' => $this->user_id,
            'apply_type' => $type,
            'request_time' => time(),
        ];
        $this->db->set($data);
        $this->db->insert('card_apply_log');
        $result = $this->db->insert_id();
        if ($result) {
            $this->load->model('template');

            $formId = $this->post('formId');
            $this->load->model('card_model');
            $card = $this->card_model->getOne($id);

            $this->template->exchange($card['user_id'], $this->user['name'], $formId);
            return $this->send_success();
        } else {
            return $this->send_fail();
        }
    }
    public function exchange_get()
    {
        if ($this->user_token == false or $this->user_id == false) {
            return $this->send_fail();
        }
        $query = $this->db->from('card')->where('status', 1)->where('user_id', $this->user_id)->get();
        $result = $query->result_array() ?: [];
        if ($result == false) {
            return $this->send_success([]);
        }
        $status = $this->get('status');
        $this->db->from('card_apply_log')->where_in('card_id', array_column($result, 'id'));
        // $this->db->join('wechat_user', 'wechat_user.id = card_apply_log.requester');
        $this->db->join('card as c', 'c.user_id = card_apply_log.requester AND c.status=1');
        if (strlen($status)) {
            $this->db->where('card_apply_log.reply_state', $status);
        }
        $page = (int) $this->get('page') ?: 1;
        $limit = (int) $this->get('limit');
        $offset = ($page - 1) * $limit;
        $this->db->limit($limit, $offset);
        $this->db->select('card_apply_log.*,c.name,c.avatar');
        $query = $this->db->get();
        $result = $query->result_array();
        return $this->send_success($result);
    }
    public function exchange_put()
    {
        $this->load->model('card_model');
        if ($this->user_token == false or $this->user_id == false) {
            return $this->send_fail();
        }
        $id = $this->put('id');
        $status = $this->put('status');
        $data = [
            'reply_state' => $status,
            'reply_time' => time(),
        ];
        $result = $this->db->where('id', $id)->set($data)->update('card_apply_log');
        if ($result) {
            $this->load->model('template');
            $formId = $this->put('formId');
            $query = $this->db->from('card_apply_log')->where('id', $id)->limit(1)->get();
            $apply_log = $query->row_array();
            if ($apply_log == false) {
                return $this->send_fail();
            }
            if ($status == 1) {
                // 收藏别人的
                // $query = $this->db->from('card')->where('user_id', $apply_log['requester'])->where('status', 1)->order_by('id DESC')->limit(1)->get();
                $card = $this->card_model->getOneByUserId($apply_log['requester']);
                if ($card) {
                    $this->collectCard($card['id'], $this->user_id, $apply_log['requester'], $card['name']);
                }
                // 别人收藏
                $this->collectCard($apply_log['card_id'], $apply_log['requester'], $this->user_id, $card['name']);
                $r = $this->template->agreeExchange($apply_log['requester'], $apply_log['card_id'], $this->user['name'], $formId);
            } else {
                $r = $this->template->refuseExchange($apply_log['requester'], $this->user['name'], $formId);
            }
            return $this->send_success();
        } else {
            return $this->send_fail();
        }
    }
    /**
     * 收藏名片
     * @param  [type]  $card_id [description]
     * @param  [type]  $user_id [description]
     * @param  integer $owner   [description]
     * @param  [type]  $name    [description]
     * @return [type]           [description]
     */
    public function collectCard($card_id, $user_id, $owner = 0, $name)
    {
        $status = 1;
        $query = $this->db->from('card_part')->where('handler', $user_id)->where('card_id', $card_id)->limit(1)->get();
        $part = $query->row_array();
        $result = false;
        if ($part) {
            if ($part['collected'] != $status) {
                $result = $this->db->where('id', $part['id'])->set('collected', $status)->update('card_part');
            }
        } else {
            $part = [
                'owner' => $owner,
                'handler' => $user_id,
                'card_id' => $card_id,
                'collected' => $status,
                'update_time' => time(),
            ];
            $this->db->set($part);
            $this->db->insert('card_part');
            $result = $this->db->insert_id();
        }

        return $result;
    }
}
