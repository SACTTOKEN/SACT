<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-20 13:45:56
 * Desc: 商品模型
 */

namespace app\model;


class product extends BaseModel
{
    public $title = 'product';
    public $field = ['id','group_people','group_face','sid', 'price', 'title', 'piclink', 'is_coupon', 'discount_number', 'discount', 'send_score', 'invent_sale', 'real_sale', 'stock', 'sub_title', 'score_rob', 'discount_rob', 'discount_limit','show','ly','video_pic','come_sid','come_pid'];


    /**
     * 按商品名称找id
     */
    public function find_pid($name)
    {
        $pid = $this->get($this->title, 'id', ["AND" => ['title' => $name]]);
        return $pid;
    }

    /*模糊查找商品id*/
    public function find_mf_pid($name)
    {
        $pid = $this->select($this->title, 'id', ["AND" => ['title[~]' => $name]]);
        return $pid;
    }


    /**
     * 模型查找id数据
     * @param id 数字
     * @return data 返回一条数据
     */
    public function find_order($id)
    {
        $data = $this->get($this->title, '*', ["AND" => ['id' => $id, 'show' => 1]]);
        return $data;
    }


    /**
     * 模型查找id数据
     * @param id 数字 field 查找字段
     * @return data 返回该字段的值
     */
    public function findme($id, $field)
    {
        $data = $this->get($this->title, $field, ["AND" => ['id' => $id]]);
        return $data;
    }



    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function admin_lists($page = 1, $number = 10, $where_base = [])
    {
        $startRecord = ($page - 1) * $number;
        $where_other = ['ORDER' => ["sort" => "DESC", "id" => "DESC"], "LIMIT" => [$startRecord, $number]];
        $where = array_merge($where_base, $where_other);
        $data_ar = $this->select($this->title, 'id', $where);
        $where_ar['id'] = $data_ar;
        if (isset($where['ORDER'])) {
            $where_ar['ORDER'] = $where['ORDER'];
        }
        $data = $this->select($this->title, '*', $where_ar);
        return $data;
    }


    /**
     * 商品表是否有该类别
     * @param cate_id 类别id
     * @return bool
     */
    public function is_have_cate($cate_id = '')
    {
        $data = $this->get($this->title, ['id'], ["AND" => ['cate_id' => $cate_id]]);
        if ($data) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists($page = 1, $number = 10, $where_base = [])
    {
        $startRecord = ($page - 1) * $number;
        $where_other = ['ORDER' => ["sort" => "DESC", "id" => "DESC"], "LIMIT" => [$startRecord, $number]];
        $where = array_merge($where_other,$where_base);

        $data_ar = $this->select($this->title, 'id', $where);
        $where_ar['id'] = $data_ar;
       
        if (isset($where['ORDER'])) {
            $where_ar['ORDER'] = $where['ORDER'];
        }
        $data = $this->select($this->title, $this->field, $where_ar);

        return $data;
    }

    public function types($iden = '')
    {

        $data[0] = '普通商品';
        $data[1] = '会员商品';
        if (plugin_is_open('jfyx')) {
            $data[2] = '积分兑换';
        }
        if (plugin_is_open('hykj')) {
            $data[3] = '好友砍价';
        }
        if (plugin_is_open('ptgw')) {
            $data[4] = '拼团购物';
        }
        if (plugin_is_open('jpsc')) {
            $data[5] = '竞拍商城';
        }
        if (plugin_is_open('xsmk')) {
            $data[6] = '预售模块';
        }
        if (plugin_is_open('xsth')) {
            $data[7] = '限时特惠';
        }
        if (plugin_is_open('shbxt')) {
            $data[8] = '限时特惠';
        }

        if ($iden === '') {
            return $data;
        } else {
            return $data[$iden];
        }
    }


    /**
     * 首页商品推荐，天天惊喜的商品
     */
    public function homepage_pro($show)
    {
        $data = $this->select($this->title, $this->field, [$show => 1]);
        $data = $this->format($data);
        return $data;
    }

    /**
     * 页面商品
     */
    public function page($iden)
    {
        $where['show'] = 1;
        $where['is_check'] = 1;
        $where['iden'] = $iden;
        $where['ORDER'] = ["sort" => "DESC", "id" => "DESC"];
        $data = $this->select($this->title, $this->field, $where);
        $data = $this->format($data);
        return $data;
    }

    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists_by_mobile($page = 1, $number = 10, $where_base = [])
    {
        $startRecord = ($page - 1) * $number;
        $where_other = ["LIMIT" => [$startRecord, $number]];
        $where = array_merge($where_base, $where_other);

        $data_ar = $this->select($this->title, 'id', $where);
        $where_ar['id'] = $data_ar;
        if (isset($where['ORDER'])) {
            $where_ar['ORDER'] = $where['ORDER'];
        }
        $data = $this->select($this->title, $this->field, $where_ar);

        $data = $this->format($data);
        return $data;
    }


    public function lists_tj($where = [], $number = 10)
    {
        $where['LIMIT'] = [1, $number];
        $data = $this->rand($this->title, $this->field, $where);
        $data = $this->format($data);
        return $data;
    }

    public function supplier_lists($where = [])
    {
        $data = $this->select($this->title, $this->field, $where);
        $data = $this->format($data);
        return $data;
    }

    public function format($data)
    {
        foreach ($data as &$vo) {
            $vo['label'] = '';
            if ($vo['is_coupon'] == 1) {
                $vo['label'] = $vo['label'] . "<span>满减券</span>";
            }
            if ($vo['discount_number'] > 0 && $vo['discount'] < 10) {
                $vo['label'] = $vo['label'] . "<span>满折券</span>";
            }
            if ($vo['send_score'] > 0) {
                $vo['label'] = $vo['label'] . "<span>送积分</span>";
            }
            $vo['sale'] = $vo['invent_sale'] + $vo['real_sale'];
        }
        return $data;
    }
}
