<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 卡密
 */

namespace app\ctrl\admin;

use app\validate\DelValidate;
use app\validate\IDMustBeRequire;

class card extends BaseController
{
    public $card_M;
    public function __initialize()
    {
        $this->card_M = new \app\model\card();
    }

    /*查某一类*/
    public function lists()
    {
        (new \app\validate\AllsearchValidate())->goCheck();
        (new \app\validate\PageValidate())->goCheck();

        $username = post('username');
        $nickname = post('nickname');
        $title = post('title');
        $oid = post('oid');
        $status = post('status');
        $where = [];
        $user_M = new \app\model\user();
        if ($username) {
            $where['uid'] = $user_M->find_mf_uid($username);
        }
        if ($nickname) {
            $where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }
        if ($title) {
            $where['pid'] = (new \app\model\product())->lists_all(['title[~]' => $title], 'id');
        }
        if ($oid) {
            $where['oid[~]'] = $oid;
        }
        if (is_numeric($status)) {
            $where['status'] = $status;
        }

        $open_time_begin = post('open_time_begin');
        $open_time_end = post('open_time_end');
        if (is_numeric($open_time_begin)) {
            $open_time_end = $open_time_end ? $open_time_end : time();
            $open_time_end = $open_time_end + 3600 * 24;
            $where['open_time[<>]'] = [$open_time_begin, $open_time_end];
        }

        $page = post("page", 1);
        $page_size = post("page_size", 10);
        $order = ['id' => 'DESC'];
        $data = $this->card_M->lists_sort($page, $page_size, $where, $order);
        $product_M = new \app\model\product();
        foreach ($data as &$one) {
            $users = user_info($one['uid']);
            $one['username'] = $users['username'];
            $one['nickname'] = $users['nickname'];
            $one['avatar'] = $users['avatar'];
            if ($one['pid']) {
                $one['title'] = $product_M->find($one['pid'], 'title');
            }
        }
        $count = $this->card_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count / $page_size);
        $res['page'] = $page;
        $res['data'] = $data;
        return $res;
    }

    /*批量删除*/
    public function del_all()
    {
        (new DelValidate())->goCheck();
        $id_str = post('id_str');
        $id_ar = explode('@', $id_str);
        $card_res = $this->card_M->is_have(['id' => $id_ar, 'status' => 1]);
        if ($card_res) {
            error('无法删除已发放卡密', 404);
        }
        $res = $this->card_M->del($id_ar);
        empty($res) && error('删除失败', 400);
        admin_log('删除卡密', $id_str);
        return $res;
    }


    //步骤一 获取excel中活动表
    public function excel_get_sheet()
    {
        $filepath = post('filename', '');
        $filepath =  IMOOC . str_replace('/api/', 'public/', $filepath);
        $res = (new \core\lib\phpexcel())->excel_get_sheet($filepath);
        return $res;
    }


    //步骤二 获取excel的第一行字段做 下拉选项
    public function excel_row_one()
    {
        $sheet_name = post('sheet', 'Sheet1');
        $filepath = post('filename', '');
        $filepath =  IMOOC . str_replace('/api/', 'public/', $filepath);
        //$filepath = IMOOC.'public/resource/excel/demo.xlsx';
        $ar = (new \core\lib\phpexcel())->wlw_excel_in($filepath, $sheet_name);
        return $ar[1];
    }

    public function show_title()
    {
        $ar = [
            ['title' => '密匙', 'iden' => 'key'],
        ];
        return $ar;
    }

    //步骤三 
    public function excel_in()
    {
        $id = post('id');
        (new IDMustBeRequire())->goCheck();
        $product_res = (new \app\model\product())->is_find($id);
        empty($product_res) && error('商品不存在',404);

        $sheet_name = post('sheet', 'Sheet1');
        $filepath = post('filename', '');
        $filepath =  IMOOC . str_replace('/api/', 'public/', $filepath);
        $iden_ar = post(['key']);
        $iden_ar = array_filter($iden_ar); //去除空数组,下标不变
        $ar = (new \core\lib\phpexcel())->wlw_excel_in($filepath, $sheet_name);

        $new_ar = [];
        $new_iden = array_flip($iden_ar); //键名和键值互换

        foreach ($ar as $key => $one) {  //键名关系映射
            if ($key == 1) {
                foreach ($one as $num => $title) {
                    $title = trim($title);
                    if ($new_iden[$title] != "") {
                        $new_key = $new_iden[$title]; // rating_cn
                        $new_ar[$new_key] = $num; //$new_ar['rating_cn'] =10                                                                                              
                    }
                }
            }
        }
        foreach ($ar as $key => $one) {
            foreach ($one as $num => $title) {
                foreach ($new_ar as $rs_key => $rs) {
                    if ($rs == $num) {
                        $my_ar[$key - 1][$rs_key] = $title;
                    }
                }
            }
        }
        unset($my_ar[0]);
        foreach ($my_ar as &$one) {
            $one['pid'] = $id;
            $res = $this->card_M->save($one);
        }
        return $res;
    }
}
