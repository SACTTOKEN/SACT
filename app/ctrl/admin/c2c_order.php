<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-09 15:01:31
 * Desc: c2c交易订单
 */
namespace app\ctrl\admin;

use app\model\c2c_order as C2cOrderModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\C2cValidate;


class c2c_order extends BaseController{
	
	public $c2c_order_M;
	public function __initialize(){
		$this->c2c_order_M = new C2cOrderModel();
	}

	//修改申述状态
	public function saveedit()
	{
		$id = post('id');
		$state=post('state');
		$admin_remark=post('admin_remark');
		(new IDMustBeRequire())->goCheck();
		(new C2cValidate())->goCheck('saveedit');
		$where_order['id']=$id;
		$where_order['state']=[1,2];
		$where_order['status']=[1,2];
		$order_ar = $this->c2c_order_M->have($where_order);
		empty($order_ar) && error('订单不存在或已完成',400);  

		$sell_integrity = user_info($order_ar['uid_sell'],'integrity');
		$buy_integrity = user_info($order_ar['uid_buy'],'integrity');

		$redis = new \core\lib\redis();
		$Model = new \core\lib\Model();
		$Model->action();
		$redis->multi();
		if($state==3){
			//买家胜诉确认订单，扣卖家诚信值
       		$c2c_S = new \app\service\c2c();
			$c2c_S->carry_out($order_ar);
			$data['remark']='买家胜诉确认订单';
			$this->c2c_order_M->up($order_ar['id'],$data);

			//判断金额
			if($sell_integrity>0){
			$money_S = new \app\service\money();
			$money_S->minus($order_ar['uid_sell'],1,'integrity','coin_c2c',$order_ar['oid_sell'],$order_ar['uid_buy'],'买家胜诉确认订单'); //记录资金流水
			}
		}elseif($state==4){
			//卖家胜诉取消订单，扣买家诚信值
			$c2c_S = new \app\service\c2c();
			$c2c_S->cancel($order_ar);
			$data['remark']='卖家胜诉取消订单';
			$this->c2c_order_M->up($order_ar['id'],$data);

			//判断金额
			if($buy_integrity>0){
			$money_S = new \app\service\money();
			$money_S->minus($order_ar['uid_buy'],1,'integrity','coin_c2c',$order_ar['oid_buy'],$order_ar['uid_sell'],'卖家胜诉取消订单'); //记录资金流水
			}
		}
		admin_log('管理员判定申述',$id);  
		$data['state']=$state;
		$data['admin_remark']=$admin_remark;
		$res=$this->c2c_order_M->up($id,$data);
	
		empty($res) && error('修改失败',404);
		$Model->run();
		$redis->exec();
		return "修改成功";
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->c2c_order_M->find($id);

		$users=user_info($data['uid_buy']);
    	$data['uid_buy_cn'] = $users['username'];
    	$data['buy_nickname'] = $users['nickname'];

		$users=user_info($data['uid_sell']);
    	$data['uid_sell_cn'] = $users['username'];
    	$data['sell_nickname'] = $users['nickname'];

		$data['fee'] = sprintf('%.2f',$data['fee']);
    	$data['price'] = sprintf('%.2f',$data['price']);
    	$data['money'] = sprintf('%.2f',$data['money']);
		
		//收款信息
        $manner_ar=explode("@",$data['manner']);
        foreach($manner_ar as $vo){
            switch ($vo)
            {
            case '支付宝':
                $data['alipay']=$users['alipay'];
                $data['alipay_name']=$users['alipay_name'];
                $data['alipay_pic']=$users['alipay_pic'];
                break;  
            case '微信':
                $data['wechat']=$users['wechat'];
                $data['wechat_pic']=$users['wechat_pic'];
                break;
            case '银行卡':
                $data['bank']=$users['bank'];
                $data['bank_card']=(string)$users['bank_card'];
                $data['bank_network']=$users['bank_network'];
                $data['bank_name']=$users['bank_name'];
                $data['bank_province'] = $users['bank_province'];
                $data['bank_city'] = $users['bank_city'];
                break;
            default:
            }
		}
		

		switch ($data['status']) {
			case '1':
				$data['status_cn'] = '交易中';
				break;
			case '2':
				$data['status_cn'] = '已付款';
				break;
			case '3':
				$data['status_cn'] = '已完成';
				break;
			case '4':
				$data['status_cn'] = '已取消';
				break;			
			
			default:
				break;
		}

		switch ($data['types']) {
			case '1':
				$data['types_cn'] = '买';
				break;
			case '2':
				$data['types_cn'] = '卖';
				break;			
			}
	
		switch ($data['state']) {
			case '0':
				$data['state_cn'] = '未申述';
				break;
			case '1':
				$data['state_cn'] = '买家申述';
				break;
			case '2':
				$data['state_cn'] = '卖家申述';
				break;
			case '3':
				$data['state_cn'] = '买家胜诉';
				break;		
			case '4':
				$data['state_cn'] = '卖家胜诉';
				break;		
			}

    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

  

	/*分页列表*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$user_M = new \app\model\user();
		$where = [];

		$oid_buy = post('oid_buy');
		$oid_sell = post('oid_sell');

		$username_1 = post('username_1');
		$nickname_1 = post('nickname_1');

		$username_2 = post('username_2');
		$nickname_2 = post('nickname_2');

		$status = post('status');

		if($oid_buy){
			$where['oid_buy[~]'] = $oid_buy;
		}

		if($oid_sell){
			$where['oid_sell[~]'] = $oid_sell;
		}

		if($username_1){	
  				$where['uid_buy'] = $user_M->find_mf_uid($username_1);
  		}

  		if($nickname_1){
  				$where['uid_buy'] = $user_M->find_mf_uid_plus($nickname_1);
  		}

  		if($username_2){	
  				$where['uid_sell'] = $user_M->find_mf_uid($username_2);
  		}

  		if($nickname_2){   				
  				$where['uid_sell'] = $user_M->find_mf_uid_plus($nickname_2);
  		}

  		if($status){
  			$where['status'] = $status;
  		}

		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->c2c_order_M->lists($page,$page_size,$where);


		foreach ($data as $key => &$one) {
			switch ($one['status']) {
			case '1':
				$one['status_cn'] = '交易中';
				break;
			case '2':
				$one['status_cn'] = '已付款';
				break;
			case '3':
				$one['status_cn'] = '已完成';
				break;
			case '4':
				$one['status_cn'] = '已取消';
				break;				
			}

			switch ($one['state']) {
			case '0':
				$one['state_cn'] = '未申述';
				break;
			case '1':
				$one['state_cn'] = '买家申述';
				break;
			case '2':
				$one['state_cn'] = '卖家申述';
				break;
			case '3':
				$one['state_cn'] = '买家胜诉';
				break;		
			case '4':
				$one['state_cn'] = '卖家胜诉';
				break;		
			}

			switch ($one['types']) {
			case '1':
				$one['types_cn'] = '买';
				break;
			case '2':
				$one['types_cn'] = '卖';
				break;			
			}

			$users=user_info($one['uid_buy']);
    		$one['uid_buy_cn'] = $users['username'];
    		$one['buy_nickname'] = $users['nickname'];

			$users=user_info($one['uid_sell']);
    		$one['uid_sell_cn'] = $users['username'];
    		$one['sell_nickname'] = $users['nickname'];

    		$one['fee'] = sprintf('%.2f',$one['fee']);
    		$one['price'] = sprintf('%.2f',$one['price']);
    		$one['money'] = sprintf('%.2f',$one['money']);

		}



		$count = $this->c2c_order_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        // var_dump($this->product_review_M->log());
        // exit();
        return $res; 
	}

//================= 以上是基础方法 ==================

}