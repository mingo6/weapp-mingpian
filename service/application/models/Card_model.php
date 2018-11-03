<?php

class Card_model extends CI_Model
{
    public function __construct()
    {

    }
    public function getOneByUserId($user_id)
    {
        $query = $this->db->from('card')->where('user_id', $user_id)->where('status', 1)->order_by('id DESC')->limit(1)->get();
        return $query->row_array() ?: [];
    }
    public function getOne($id)
    {
        $query = $this->db->from('card')->where('id', $id)->limit(1)->get();
        return $query->row_array() ?: [];
    }
}
