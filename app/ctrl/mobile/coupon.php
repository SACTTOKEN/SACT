<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 红包优惠券
 */
namespace app\ctrl\mobile;

class coupon extends BaseController{

   
	/*领取优惠券*/
	public function receive()
	{
        (new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id');
		$ar = (new \app\service\coupon())->receive($id,0,'商品页领取红包');
        return $ar; 
	}


	/*我的优惠券列表*/
	public function my_coupon_list()
	{
		$uid = $GLOBALS['user']['id'];
		$coupon_M = new \app\model\coupon();
		$product_M = new \app\model\product();

		$where['uid'] = $uid;
		$where['is_use'] = 0;
		$where['end_time[>]'] = time(); //过期的不显示

		$page=post("page",1);
		$page_size = post("page_size",10);
		$ar = $coupon_M->lists($page,$page_size,$where);

		foreach($ar as &$one){
			if($one['pid']>0){
				$p_ar = $product_M->find($one['pid'],['title','piclink']);
				$one['p_piclink'] = $p_ar['piclink'];
				$one['p_title'] = $p_ar['title'];
			}
			
		}
		return $ar;
	}


	/*我的优惠券使用记录*/
	public function my_coupon_history()
	{
		$uid = $GLOBALS['user']['id'];
		$coupon_M = new \app\model\coupon();
		$product_M = new \app\model\product();

		$where['uid'] = $uid;
		$where['OR']['is_use']  = 1; //已使用
		$where['OR']['end_time[<]'] = time(); //已过期
		$where['OR']['is_use']  = 2; //商品已删除

		$page=post("page",1);
		$page_size = post("page_size",100);
		$ar = $coupon_M->lists($page,$page_size,$where);

		foreach($ar as &$one){
				$p_ar = $product_M->find($one['pid'],['title','piclink']);
				$one['p_piclink'] = $p_ar['piclink'];
				$one['p_title'] = $p_ar['title'];
		
				if($one['is_use']==1){
					$one['info'] = '已使用';
				}else if($one['is_use']==2){
					$one['info'] = '商品已下架';
				}else if( $one['end_time']<time() && $one['is_use']==0 ){
					$one['info'] = '已过期';
				}
		}
		return $ar;
	}


	/*券集市列表  添加红包时指定商品,可用积分兑换,发行总数大于已领取数 的显示为券集市红包*/
	public function packet_list()
	{
		$packet_M = new \app\model\packet();
		$product_M = new \app\model\product();
		$where['cdn_pid[>]'] = 0;
		$where['jf_change[>]'] = 0;
		$where['AND']=['full_num[>]receive_num'];

		$page=post("page",1);
		$page_size = post("page_size",10);
		$ar = $packet_M->lists($page,$page_size,$where);
	
		foreach($ar as &$one){	
				$p_ar = $product_M->find($one['cdn_pid'],['title','piclink']);
				$one['p_piclink'] = $p_ar['piclink'];
				$one['p_title'] = $p_ar['title'];
		}
		return $ar;
	}

	/*优惠券积分兑换 参数id:packet表id*/
	public function packet_jf()
	{
		(new \app\validate\IDMustBeRequire())->goCheck();
		$packet_M = new \app\model\packet();
		$coupon_M = new \app\model\coupon();
		$user_M = new \app\model\user();
		$money_S = new \app\service\money();
		$id = post('id');
		$uid = $GLOBALS['user']['id'];
		$ar = $packet_M->find($id);
		$u_ar = $user_M->find($uid);


		//每人限领多少张
		$max_num = $ar['limit_num'];
		$where['packet_id'] = $id;
		$where['uid'] = $uid;
		$my_num = $coupon_M->new_count($where);
		if($my_num>=$max_num){
			error('每人限领'.$max_num.'张');
		}
		

		//判断积分是否足够兑换
		$need_jf = floatval($ar['jf_change']);
		$my_jf = floatval($u_ar['integral']);
		if($my_jf < $need_jf){
			error('积分不足',400);
		}

		//等级是否被限制兑换
		$my_lv = $u_ar['rating']; 	
		$min_lv = $ar['limit_lv'];
		if($min_lv>0 && $min_lv > $my_lv){
			error('会员等级不够,无法兑换');
		}

		flash_god($uid);
		//回滚BEGIN 
        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  
        $model->action();
        $redis->multi();

        $data_a['receive_num'] = $ar['receive_num'] + 1;
        $packet_M->up($id,$data_a); //已领取数加一
        $data['money'] = $ar['money'];
        $data['sid'] = $ar['cdn_sid'];
        $data['pid'] = $ar['cdn_pid'];
        $data['xfm'] = $ar['cdn_xfm'];
        $data['begin_time'] = time();
        $data['end_time'] = time() + $ar['lifetime']*24*3600;
        $data['packet_id'] = $id;
        $data['uid'] = $uid;
        $res = $coupon_M->save_by_oid($data);
        empty($res) && error('上传失败',400);
        $money = $need_jf;
        $cate = 'integral';
        $iden = 'coupon_jf';
        $oid = $res['oid'];
        $ly_id = $uid;
		$remark = '积分兑换红包';
        $money_S -> minus($uid,$money,$cate,$iden,$oid,$ly_id,$remark);

        $model->run();
        $redis->exec();
		//回滚END
		return true;
	}


	/*领取新手红包 iden:is_open_new  mobile/coupon/find_iden */ 
	public function get_new_coupon(){
		if(!plugin_is_open('xshb')){
            return false;
        }
		$user = $GLOBALS['user'];
		$uid  = $GLOBALS['user']['id'];
		$rating = $user['rating'];
		$user_attach_M = new \app\model\user_attach();

		$where['uid'] = $uid;
		$where['noob_coupon'] = 1;
		$is_have = $user_attach_M->is_have($where);

		if($is_have){
			return false;
		}

		$packet_M = new \app\model\packet();
		$coupon_M = new \app\model\coupon();

		$where_1['is_new'] = 1;
		$where_1['limit_lv[<=]'] = $rating;
		$where_1['new_rating[<=]'] = $rating;
		$rs = $packet_M -> lists_all($where_1);

		$new_ar = [];

		  	flash_god($uid);
	        $model = new \core\lib\Model();
	        $redis = new \core\lib\redis();  
	        $model->action();
	        $redis->multi();


	        foreach($rs as $key=>$one){
				$data['money'] = $one['money'];
				$data['sid'] = $one['cdn_sid'];
				$data['pid'] = $one['cdn_pid'];
				$data['xfm'] = $one['cdn_xfm'];
				$data['begin_time'] = time();
				$data['end_time'] = time() + $one['lifetime']*24*3600;
				$data['packet_id'] = $one['id'];
				$data['uid'] = $uid;
				$data['source'] = '新手红包';
				$res = $coupon_M->save_by_oid($data);
				empty($res) && error('领取失败',400);

				$new_ar[$key]['coupon_title']  = $one['title'];
				$new_ar[$key]['money'] = $one['money'];
            	$new_ar[$key]['desc'] = $one['new_desc'];
            	$new_ar[$key]['xfm'] = $one['cdn_xfm'];
            	$new_ar[$key]['end_time'] = time() + $one['lifetime']*24*3600;
	        }
	        
			$user_attach_M->up($uid,['noob_coupon'=>1]);


        	$model->run();
        	$redis->exec();

        return $new_ar;			
	}


		
}