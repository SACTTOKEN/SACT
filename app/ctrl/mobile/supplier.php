<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 17:02:46
 * Desc: 商户
 */

namespace app\ctrl\mobile;

use app\validate\IDMustBeRequire;
use app\model\user as UserModel;

class supplier extends PublicController
{
    public $user_M;
    public $user_attach_M;
    public $productM;
    public $order_M;
    public function __initialize()
    {
        if (!plugin_is_open('shbxt')) {
            error('未开启商户版本', 10007);
        }
        $this->user_M = new UserModel();
        $this->user_attach_M = new \app\model\user_attach();
        $this->productM = new \app\model\product();
        $this->order_M = new \app\model\order();
    }

    public function is_supplier()
    {
        if (!$GLOBALS['user']['is_supplier']) {
            error('还未开通供应商', 404);
        }
    }

    //申请显示字段
    public function find_apply()
    {
        if ($GLOBALS['user']['is_supplier'] == 1) {
            error(['info'=>'已经是供应商无需再审核','url'=>'/zhanye/zhanyemember'],10008);
        }
        $supplier_M = new \app\model\supplier();
        $apply_res = $supplier_M->have(['uid' => $GLOBALS['user']['id']]);
        $datas = (new \app\model\config())->list_cate('supplier_apply');
        $data['config'] = array_column($datas, null, 'iden');
        $apply_res['image'] = (new \app\model\image())->list_cate('supplier', $apply_res['id']);
        $data['content'] = $apply_res;
        return $data;
    }


    //保存申请
    public function apply()
    {
        if ($GLOBALS['user']['is_supplier'] == 1) {
            error(['info'=>'已经是供应商无需再审核','url'=>'/zhanye/zhanyemember'],10008);
        }
        $supplier_M = new \app\model\supplier();
        $apply_res = $supplier_M->have(['uid' => $GLOBALS['user']['id']]);
        if (!empty($apply_res)) {
            if ($apply_res['is_check'] == 1) {
                error('已审核通过', 404);
            } else {
                error('审核中，请等待通知', 404);
            }
        }
        $supplier_V = new \app\validate\SupplierValidate();
        if (c('supplier_company_title')) {
            $supplier_V->goCheck('supplier_company_title');
            $data['title'] = post('title');
        }
        if (c('supplier_company_region')) {
            $supplier_V->goCheck('supplier_company_region');
            $data['province'] = post('province');
            $data['city'] = post('city');
            $data['area'] = post('area');
            $data['town'] = post('town');
        }
        if (c('supplier_company_add')) {
            $supplier_V->goCheck('supplier_company_add');
            $data['add'] = post('add');
        }
        if (c('supplier_company_name')) {
            $supplier_V->goCheck('supplier_company_name');
            $data['name'] = post('name');
        }
        if (c('supplier_company_card')) {
            $supplier_V->goCheck('supplier_company_card');
            $data['card'] = post('card');
        }
        if (c('supplier_company_cardpositive')) {
            $supplier_V->goCheck('supplier_company_cardpositive');
            $data['cardpositive'] = post('cardpositive');
        }
        if (c('supplier_company_cardnegative')) {
            $supplier_V->goCheck('supplier_company_cardnegative');
            $data['cardnegative'] = post('cardnegative');
        }
        if (c('supplier_company_license')) {
            $supplier_V->goCheck('supplier_company_license');
            $data['license'] = post('license');
        }
        if (c('supplier_company_product')) {
            $supplier_V->goCheck('supplier_company_product');
        }
        $data['uid'] = $GLOBALS['user']['id'];
        $res = $supplier_M->save($data);
        empty($res) && error('申请错误', 10006);
        if (c('supplier_company_product')) {
            $image = post('image');
            $image = explode("@", $image);
            $image_M = new \app\model\image();
            foreach ($image as $vo) {
                if ($vo) {
                    $img_ar = array();
                    $img_ar['aid'] = $res;
                    $img_ar['cate'] = 'supplier';
                    $img_ar['piclink'] = $vo;
                    $image_M->save($img_ar);
                }
            }
        }
        return '申请成功';
    }


    public function complaint_tpyes()
    {
        return array(
            '卖家产品质量问题',
            '物流问题',
            '退货问题',
            '其他',
        );
    }

    //投诉
    public function complaint()
    {
        (new \app\validate\ComplaintValidate())->goCheck('add');
        $complaint_M = new \app\model\supplier_complaint();
        $data = post(['sid', 'title', 'content']);
        $com_ar = $complaint_M->is_have(['uid' => $GLOBALS['user']['id'], 'sid' => $data['sid'], 'types' => 0]);
        if ($com_ar) {
            error('投诉处理中，请勿重复提交', 404);
        }
        $data['uid'] = $GLOBALS['user']['id'];
        $res = $complaint_M->save($data);
        empty($res) && error('提交错误', 10006);
        return '投诉成功';
    }


    //商品管理
    public function product()
    {
        $this->is_supplier();
        (new \app\validate\AllsearchValidate())->goCheck();
        (new \app\validate\PageValidate())->goCheck();
        $page = post("page", 1);
        $title = post('title');
        $is_check = post('is_check');
        $is_check = $is_check ? $is_check : 1;
        if ($title) {
            $where['title[~]'] = $title;
        }
        if (is_numeric($is_check)) {
            $where['is_check'] = $is_check;
        }
        $where['sid'] = $GLOBALS['user']['id'];
        $data = $this->productM->admin_lists($page, 10, $where);
        return $data;
    }

    //添加商品
    public function addsave_product()
    {
        $this->is_supplier();
        (new \app\validate\ProductValidate())->goCheck('supplier_arr');
        $data = post(['title', 'sub_title', 'cate_id', 'price', 'cost_price', 'hid', 'is_integral', 'is_mail', 'weight']);
        $data['stock'] = post('stock', '999');
        if ($data['price'] < $data['cost_price']) {
            error('价格不能小于成本价格', 404);
        }
        $piclink = post('piclink');
        $piclink = json_decode($piclink, true);
        if (!isset($piclink[0]['piclink'])) {
            error('请上传图片', 404);
        }
        $data['piclink'] = $piclink[0]['piclink'];
        $content = post('content');
        $content = explode("@", $content);
        $data['sid'] = $GLOBALS['user']['id'];
        $data['content'] = '';
        foreach ($content as $vo) {
            if ($vo) {
                $data['content'] .= '<img @link=@"' . $vo . '"/>';
            }
        }

        flash_god($GLOBALS['user']['id']);
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $res = $this->productM->save($data);
        empty($res) && error('添加商品失败', 404);
        //sku
        $sku_ar = array();
        if (isset($data['hid'])) {
            $sku_ar['hid'] = $data['hid'];
        }
        $sku_ar['price'] = $data['price'];
        $sku_ar['cost_price'] = $data['cost_price'];
        $sku_ar['stock'] = $data['stock'];
        $sku_ar['pid'] = $res;
        $sku_res = (new \app\model\product_sku())->save($sku_ar);
        empty($sku_res) && error('添加商品失败', 404);
        //图片
        $image_M = new \app\model\image();
        foreach ($piclink as $vo) {
            $image_ar = array();
            $image_ar['aid'] = $res;
            $image_ar['cate'] = 'product';
            $image_ar['piclink'] = $vo['piclink'];
            $image_M->save($image_ar);
        }
        //cs($this->productM->log(),1);
        $Model->run();
        $redis->exec();
        return $res;
    }



    //编辑商品
    public function editproduct()
    {
        $this->is_supplier();
        $id = post('id');
        (new IDMustBeRequire())->goCheck();
        $data = $this->productM->have(['id' => $id, 'sid' => $GLOBALS['user']['id']]);
        empty($data) && error('商品不存在', 10007);

        if (!empty($data['sku_id'])) {
            error('sku商品，请在电脑端操作', 10007);
        }

        $pro_price_M = new \app\model\product_price();
        $price_res = $pro_price_M->is_have(['pid' => $data['id']]);
        if ($price_res) {
            error('会员等级价格商品，请在电脑端操作', 10007);
        }
        $pro_sku_M = new \app\model\product_sku();
        $data['sku'] = $pro_sku_M->have(['pid' => $data['id']]);
        if ($data['content']) {
            $data['content'] = str_replace('@link=@', 'src=', $data['content']);
        }
        $cate_id = $data['cate_id'];
        $product_cate_M = new \app\model\product_cate();
        $data['cate_father'] = $product_cate_M->find_father($cate_id, [$cate_id]);

        $image_M = new \app\model\image();
        $image_ar = $image_M->list_cate('product', $id);
        $data['piclink'] = $image_ar;
        return $data;
    }


    /*按id删除*/
    public function del_image()
    {
        $id = post('id');
        (new \app\validate\ImageValidate())->goCheck('scene_find');
        $image_M = new \app\model\image();
        $pid = $image_M->find($id, 'aid');
        empty($pid) && error('查找不到图片', 404);
        $where['id'] = $pid;
        $where['sid'] = $GLOBALS['user']['id'];
        $p_res = $this->productM->is_have($where);
        empty($p_res) && error('查找不到订单', 404);
        $res = $image_M->del($id);
        empty($res) && error('删除失败', 400);
        return $res;
    }


    //编辑保存商品
    public function saveedit_product()
    {
        $this->is_supplier();
        (new \app\validate\ProductValidate())->goCheck('supplier_arr');
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $product_ar = $this->productM->have(['id' => $id, 'sid' => $GLOBALS['user']['id']]);
        empty($product_ar) && error('商品不存在', 404);
        if (!empty($product_ar['sku_id'])) {
            error('sku商品，请在电脑端操作', 404);
        }
        $pro_price_M = new \app\model\product_price();
        $price_res = $pro_price_M->is_have(['pid' => $product_ar['id']]);
        if ($price_res) {
            error('会员等级价格商品，请在电脑端操作', 404);
        }

        $data = post(['title', 'sub_title', 'cate_id', 'price', 'cost_price', 'hid', 'is_integral', 'is_mail', 'weight']);
        $data['stock'] = post('stock', '999');
        if ($data['price'] < $data['cost_price']) {
            error('价格不能小于成本价格', 404);
        }
        $piclink = post('piclink');
        $piclink = json_decode($piclink, true);
        if (!isset($piclink[0]['piclink'])) {
            error('请上传图片', 404);
        }
        $data['piclink'] = $piclink[0]['piclink'];
        $content = post('content');
        $content = explode("@", $content);
        $data['content'] = '';
        foreach ($content as $vo) {
            if ($vo) {
                $data['content'] .= '<img @link=@"' . $vo . '"/>';
            }
        }

        flash_god($GLOBALS['user']['id']);
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $res = $this->productM->up($id,$data);
        empty($res) && error('添加商品失败', 404);
        //sku
        $sku_ar = array();
        if (isset($data['hid'])) {
            $sku_ar['hid'] = $data['hid'];
        }
        $sku_ar['price'] = $data['price'];
        $sku_ar['cost_price'] = $data['cost_price'];
        $sku_ar['stock'] = $data['stock'];
        $sku_res = (new \app\model\product_sku())->up_all(['pid' => $id], $sku_ar);
        empty($sku_res) && error('添加商品失败', 404);
        //图片
        $image_M = new \app\model\image();
        foreach ($piclink as $vo) {
            if ($vo['id'] == 0) {
                $image_ar = array();
                $image_ar['aid'] = $id;
                $image_ar['cate'] = 'product';
                $image_ar['piclink'] = $vo['piclink'];
                $image_M->save($image_ar);
            } else {
                $image_M->up($vo['id'], ['piclink' => $vo['piclink']]);
            }
        }
        //cs($this->productM->log(),1);
        $Model->run();
        $redis->exec();
        return $res;
    }


    /*按id删除*/
    public function del_product()
    {
        $id = post('id');
        (new IDMustBeRequire())->goCheck();
        $rs = $this->productM->have(['id' => $id, 'sid' => $GLOBALS['user']['id']]);
        empty($rs) && error('商品不存在', 404);
        //判断是否有红包是这个商品有的话提示有单品红包不让删除
        $this->check_have_coupon($id);

        //回滚开始
        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();
        $model->action();
        $redis->multi();
        //没有的话删除的时候把会员领取的红包未使用的is_use状态给2
        $this->change_coupon($id);

        $where['pid'] = $id;
        (new \app\model\product_sku())->del_all($where);
        (new \app\model\product_attr())->del_all($where);
        (new \app\model\product_price())->del_all($where);
        $where_pic = (new \app\model\product_review())->find($where, 'id');
        (new \app\model\product_review_pic())->del_all($where_pic);
        (new \app\model\product_review())->del_all($where);
        $res = $this->productM->del($id);
        empty($res) && error('删除失败', 400);

        $model->run();
        $redis->exec();
        //回滚END
        return $res;
    }

    public function check_have_coupon($id)
    {
        $packet_M = new \app\model\packet();
        $where['cdn_pid'] = $id;
        $is_have = $packet_M->is_have($where);
        $is_have && error('商品有单品红包不能删除', 400);
    }

    //会员领取的红包未使用的is_use状态给2
    public function change_coupon($id)
    {
        $coupon_M = new \app\model\coupon();
        $where['pid'] = $id;
        $where['is_use'] = 0;
        $rs = $coupon_M->lists_all($where);
        $data['is_use'] = 2;
        if ($rs) {
            foreach ($rs as $one) {
                $coupon_M->up($one['id'], $data);
            }
        }
    }


    //商户中心 咨询量，浏览量，货款未做
    public function member()
    {
        $this->is_supplier();
        $order_M = new \app\model\order();
        $drag_num_M = new \app\model\drag_num();
        $user = $GLOBALS['user'];
        $data['resources'] = $this->productM->new_count(['sid' => $user['id']]);   //资源量
        $data['order_quantity'] = $order_M->new_count(['sid' => $user['id'], 'is_pay' => 1]);   //订单量
        $data['pageviews'] =$drag_num_M->find_sum('hit_num',['sid'=>$user['id']]);   //浏览量

        $user_M = new \app\model\user();
        $push_num = $user_M->find($user['id'],'push_num');
        $data['push_num'] = intval($push_num);
        
        $data['consultation_volume'] = $drag_num_M->find_sum('ask_num',['sid'=>$user['id']]);;   //咨询量
        $data['closed_payment'] = $user['sum_supply'];   //已结货款
        $cost= $order_M->find_sum('cost', ['sid' => $user['id'], 'is_pay' => 1, 'is_settle' => 0]); //未结货款
        $data['outstanding_payment'] =$cost-$cost*$user['shop_fee']/1000-$cost*$user['shop_referrer']/1000;
        if($data['outstanding_payment']<0){
            $data['outstanding_payment']=0;
        }
        $data['delivered'] = $order_M->new_count(['sid' => $user['id'],'status' =>['已支付','配货中']]); //未结货款

        if ($data['pageviews'] == 0) {
            $data['conversion_rate'] = '0%';
        } else {
            $data['conversion_rate'] = ($data['consultation_volume'] / $data['pageviews'] * 100) . '%';   //转化率
        }




        $where['is_ask'] = 1;
        $where['sid'] = $GLOBALS['user']['id'];
        $where2['is_msg'] = 1;
        $where2['sid'] = $GLOBALS['user']['id'];
        $drag_M = new \app\model\drag();
        $ask = $drag_M->new_count($where);
        $msg = $drag_M->new_count($where2);
        $data['ask'] = $ask;
        $data['msg'] = $msg;

        return $data;
    }

    //订单
    public function order()
    {
        (new \app\validate\AllsearchValidate())->goCheck();
        (new \app\validate\PageValidate())->goCheck();
        $oid = post('oid');
        $status = post('status');
        if ($status) {
            if($status == '未完成') {
                $where['status[!]'] = '已完成';
            }elseif($status == '退货中') {
                $where['is_return'] = 1;
            }elseif($status=='已支付'){
				$where['status'] = ['已支付','配货中'];
			}else{
                $where['status'] = $status;
            }
        }
        if ($oid) {
            $where['OR']['oid[~]'] = $oid;
            $where['OR']['oid'] =(new \app\model\order_product())->lists_all(['title[~]' => $oid], 'oid');
        }
        $where['sid'] = $GLOBALS['user']['id'];
        $where['is_pay'] = 1;
        $where['AND']['status[!]'] = ['已关闭', '未支付'];

        $page = post("page", 1);
        $page_size = post("page_size", 10);
        $data = $this->order_M->lists($page, $page_size, $where);
        foreach($data as &$vo){
            $users=user_info($vo['uid']);
            $vo['sid_cn']=$users['nickname']?$users['nickname']:$users['username']; 
        }
        return $data;
    }

    //订单详情
    public function order_details()
    { 
        (new \app\validate\IDMustBeRequire())->goCheck();
		$id=post("id");
		$where['is_pay']=1;
		$where['id']=$id;
		$where['sid']=$GLOBALS['user']['id'];
		$data = $this->order_M->have($where);
		empty($data) && error('订单不存在',10007);
        $data['mail'] =  (new \app\service\kdn_inquire())->index($data);
		$product = (new \app\model\order_product())->find_by_oid($data['oid']);   
		foreach($product as &$rs){
			if(!($data['status']!='已发货' && $data['status']!='已支付' && $data['status']!='配货中')){
				switch ($rs['status'])
				{
				case 1:
					$rs['order_return']='确认退货';
					break;
				case 2:
					$rs['order_return']='提交运单中';
					break;
				case 3:
					$rs['order_return']='确认退款';
					break;
				case 4:
					$rs['order_return']='退货成功';
					break;
				default:
				}
			}
		}
        $data['product'] =  $product;
        $users=user_info($data['uid']);
	    $data['sid_cn'] = $users['nickname']?$users['nickname']:$users['username']; 
		return $data;
    }

    
    //退货申请
    public function application()
    {
        (new IDMustBeRequire())->goCheck();
        $uid=$GLOBALS['user']['id'];
        $id=post('id');
        $where['id']=$id;
        $where['status[!]']=0;
        $order_pro_ar=(new \app\model\order_product())->have($where);
        empty($order_pro_ar) && error('不是退货状态',10007);
        $where_ar['sid']=$uid;
        $where_ar['oid']=$order_pro_ar['oid'];
        $order_ar=$this->order_M->have($where_ar);
        empty($order_ar) && error('订单不存在',10007);
        
		$order_S=new \app\service\order();
        if($order_ar['status']!='已发货' && $order_ar['status']!='已支付' && $order_ar['status']!='配货中'){
            $order_S->order_is_raturn($order_ar);
            error('订单'.$order_ar['status'].'已取消退货',10007);
        }
        if(($order_ar['types']==1 && c('fksfjfl')==1) || $order_ar['is_settle']>0){
            $order_S->order_is_raturn($order_ar);
            error('订单已取消退货',10007);
        }
        $order_pro_ar['pic']=(new \app\model\image())->list_cate('order_return',$order_pro_ar['id']);
        $order_pro_ar['reason']=['退运费','商品成分描述不符','生产日期/保质期与商品描述不符','图片/产地/批号/规格等描述不符','质量问题'];
        $order_pro_ar['order_ar']=$order_ar;
        return $order_pro_ar;
    }

    //确认退货
    //  `status` tinyint(1) DEFAULT '0' COMMENT '0正常，1申请退货，2允许退货，3已退货待退款 4退货成功',
    public function confirm_order_return()
    {
        $id=post('id');
        $order_pro_ar=$this->application();
        $status=post('status');

		switch ($status) {
			case "0":
				if ($order_pro_ar['status'] != 1 && $order_pro_ar['status'] != 3) {
					error('用户退货中', 404);
				}
                $order_S=new \app\service\order();
				$order_S->order_is_raturn($order_pro_ar['order_ar']);
				break;
			case "2":
				if ($order_pro_ar['status'] != 1) {
					error('用户退货中', 404);
				}
				(new \app\validate\OrderProductValidate())->goCheck('reback_goods');
				$parameter = post(['return_name', 'return_tel', 'return_address']);
				$data['return_name'] = $parameter['return_name'];
				$data['return_tel'] = $parameter['return_tel'];
				$data['return_address'] = $parameter['return_address'];
				break;
			case "4":
				if ($order_pro_ar['status'] != 3) {
					error('用户未提交退货单号', 404);
				}
				//退货退款
                $order_S=new \app\service\order();
				$return_res=$order_S->return_order($order_pro_ar['order_ar'],$order_pro_ar);
				if($return_res!==true){
					error($return_res,404);
				}
				//退货退款end
				$order_S->order_is_raturn($order_pro_ar['order_ar']);
				break;
			default:
				error('用户退货中', 404);
		}
		$data['status']	= $status;
		$data['return_time'] = time();
		$data['admin_remark'] = post('admin_remark', '');
		$res = (new \app\model\order_product())->up($id, $data);
		empty($res) && error('修改失败', 400);
		return $res;
    }

    //发货
    public function order_ship()
    {
        (new IDMustBeRequire())->goCheck();
		$id = post('id');
		$mail_type = post('mail_type'); //发货方式  0：常规  1：电子面单
		if ($mail_type == 0) {
			(new \app\validate\OrderValidate())->goCheck('scene_edit_send');
			$data = post(['mail_oid']);
            $order_ar = $this->order_M->have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
            empty($order_ar) && error('订单不存在',404);
			if ($order_ar['status'] == '未支付' || $order_ar['status'] == '已关闭') {
				error('订单未支付', 400);
			}
			if ($data['mail_oid'] != '' && $order_ar['status'] == '已支付') {
				$data['status'] = '已发货';
				$data['mail_time'] = time();
				$data['mail_courier'] = (new \app\model\mail())->have(['sid' => $order_ar['sid']], 'title');
			}
			mb_sms('ship',$id);
			$res = $this->order_M->up($id,$data);
			empty($res) && error('修改失败', 400);
			return $data;
		} else {
			$kdn_S = new \app\service\kdn();
			$order_ar = $this->order_M->have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
            empty($order_ar) && error('订单不存在',404);
			$data_up = $kdn_S->ship($id);
          	if($data_up!==true){
            	error($data_up, 400);
            }
			mb_sms('ship',$id);
			return '发货成功';
		}
    }

    //修改信息页面
    public function edit_info()
	{
        $id=$GLOBALS['user']['id'];
		$data=$this->user_attach_M->find_supplier(['uid'=>$id]);
        return $data; 
    }
    
    //保存修改信息
    public function saveedit_info()
    {
		(new \app\validate\UserAttachValidate())->goCheck('supplier');
        $id=$GLOBALS['user']['id'];
        $data=post(['shop_title','shop_logo','shop_wechat','shop_recommend','shop_tel','shop_province','shop_city','shop_area','shop_town','shop_address','shop_cate','shop_longitude','shop_latitude']);
        $res=$this->user_attach_M->up($id,$data);
        empty($res) && error("修改失败",404);
        return $res; 
    }

    //列表
    public function page()
    { 
        $data=(new \app\service\page())->redis_supplier_page();
        return $data;
    }

    //详情
    public function details()
    {
        $id=post('id');
        if(!$id){
            error('商铺不存在',10007);
        }
        (new IDMustBeRequire())->gocheck();


        $data=$this->user_attach_M->find_supplier(['uid'=>$id]);
        $data['nickname'] = user_info($id,'nickname');
        $data['im'] = user_info($id,'im');
        return $data;
    }

    //商户数据分析
    public function analysis()
    {
        $this->is_supplier();
    }

    

    //向商家付款
    public function pay(){
        $id=post('id');
        $money=post('money');
        (new IDMustBeRequire())->goCheck();
        (new  \app\validate\SupplierValidate())->goCheck('pay');
        $s_ar=$this->user_M->find($id);
        if($s_ar['is_supplier']!=1){
            error('不算供应商',404);
        }
        $order_M = new \app\model\order();
        $order_pro_M = new \app\model\order_product();
        $uid = $GLOBALS['user']['id'];
        
        //写入订单
        $order['integral_dk_money']=0;
        $order['integral_dk_per']=0;
        $order['red_money']=0;
        $order['uid']=$uid;
        $order['sum_price']=$money;
        $order['cost']=$money;
        $order['sum_mail']=0;
        $order['sid']=$id;
        $order['is_virtual']=1;
        $order['types']=8;
        $order['types_cn']=(new \app\model\product())->types(8);
        $order['cid']=0;
        $order['money']=$money;
        $order['status']='未支付';
        $or_res=$order_M->save_by_oid($order);
        empty($or_res) && error('写入订单失败',400);   
        
        $product=[];
        $product['oid']=$or_res['oid'];
        $product['pid']=0;
        $product['title']='商家线下付款';
        $product['piclink']=$s_ar['avatar'];
        $product['cost']=$money;
        $product['price']=$money;
        $product['uid']=$uid;
        $product['sid']=$id;
        $product['number']=1;
        $product['money']= $money;
        $res=$order_pro_M->save($product);
        empty($res) && error('写入订单商品失败',400);  
        return $or_res['id'];
    }

}
