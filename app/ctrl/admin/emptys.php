<?php

/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 管理员类
 */

namespace app\ctrl\admin;

class emptys extends BaseController
{

	public function __initialize()
	{
		if ($GLOBALS['admin']['role_con'] != 'god') {
			return;
		}
	}

	//重置用户关系表
	public function user_gx()
	{
		$Model = new \core\lib\Model();
		$Model::$medoo->query("truncate table user_gx");
		$Model::$medoo->query("insert into user_gx(uid,tid,rating,coin_rating,t_rating,t_coin_rating,`level`) select id,tid,rating,coin_rating,(select rating from user where id=a.tid),(select coin_rating from user where id=a.tid),1 from user as a where a.tid>0");
		$user_gx_M = new \app\model\user_gx();
		for ($i = 1; $i <= 100; $i++) {
			if (!($user_gx_M->is_have(['level[>=]' => $i]))) {
				break;
			}
			$Model::$medoo->query("insert into user_gx(uid,tid,rating,coin_rating,t_rating,t_coin_rating,`level`) select temp.uid,temp.tid,temp.rating,temp.coin_rating,(select rating from user where id=temp.tid),(select coin_rating from user where id=temp.tid),temp.level from (select uid,(select tid from user where id=user_gx.tid) as tid,rating,coin_rating," . ($i + 1) . " as level from user_gx where level=" . $i . ") temp where temp.tid>0 ");
		}
	}

	//重置用户统计信息
	public function user_attach()
	{
		$Model = new \core\lib\Model();
		$redis = new \core\lib\redis();
		$user_ar = (new \app\model\user())->lists_all([], 'id');
		foreach ($user_ar as $vo) {
			$rd_name = 'user:' . $vo;
			$redis->hdel($rd_name);
		}
		$Model::$medoo->query("update user_attach set ynumber=(select count(id) from user_gx where tid=user_attach.uid and level=1)");	//一级人数
		$Model::$medoo->query("update user_attach set znumber=(select  count(id) from user_gx where tid=user_attach.uid)");	//总人数
		$Model::$medoo->query("update user_attach set yvip=(select count(id)  from user_gx where tid=user_attach.uid and level=1 and rating>1)");	//一级分销人数
		$Model::$medoo->query("update user_attach set zvip=(select  count(id) from user_gx where tid=user_attach.uid and rating>1)");	//总分销人数
		$Model::$medoo->query("update user_attach set coin_yvip=(select  count(id) from user_gx where tid=user_attach.uid and level=1 and coin_rating>1)");	//矿机一级分销人数
		$Model::$medoo->query("update user_attach set coin_zvip=(select count(id)  from user_gx where tid=user_attach.uid and coin_rating>1)");	//矿机总分销人数
		
		$Model::$medoo->query("update user_attach set vip_yvip=(select  count(id) from user_gx where tid=user_attach.uid and level=1 and vip_rating>1)");	//Vip一级分销人数
		$Model::$medoo->query("update user_attach set vip_zvip=(select count(id)  from user_gx where tid=user_attach.uid and vip_rating>1)");	//VIP总分销人数
		
		
	}


	//清空数据
	public function index()
	{
		$Model = new \core\lib\Model();
		$redis = new \core\lib\redis();

		$user_ar = (new \app\model\user())->lists_all([], 'id');
		foreach ($user_ar as $vo) {
			$rd_name = 'user:' . $vo;
			$redis->hdel($rd_name);
		}
		$Model::$medoo->query("truncate table  admin_log");
		$Model::$medoo->query("truncate table  c2c_buy");
		$Model::$medoo->query("truncate table  c2c_order");
		$Model::$medoo->query("truncate table  cart");
		$Model::$medoo->query("truncate table  coin_exchange");
		$Model::$medoo->query("truncate table  coin_order");
		$Model::$medoo->query("truncate table  coin_recharge");
		$Model::$medoo->query("truncate table  coin_withdraw");
		$Model::$medoo->query("truncate table  coupon");
		$Model::$medoo->query("truncate table  extract");
		$Model::$medoo->query("truncate table  iorder");
		$Model::$medoo->query("truncate table  money");
		$Model::$medoo->query("truncate table  money_10");
		$Model::$medoo->query("truncate table  money_9");
		$Model::$medoo->query("truncate table  money_8");
		$Model::$medoo->query("truncate table  money_7");
		$Model::$medoo->query("truncate table  money_6");
		$Model::$medoo->query("truncate table  money_5");
		$Model::$medoo->query("truncate table  money_4");
		$Model::$medoo->query("truncate table  money_3");
		$Model::$medoo->query("truncate table  money_2");
		$Model::$medoo->query("truncate table  money_1");
		$Model::$medoo->query("truncate table  iorder_product");
		$Model::$medoo->query("truncate table  plugin_early_lord");
		$Model::$medoo->query("truncate table  plugin_early_slave");
		$Model::$medoo->query("truncate table  plugin_finger_lord");
		$Model::$medoo->query("truncate table  plugin_guess_lord");
		$Model::$medoo->query("truncate table  plugin_guess_slave");
		$Model::$medoo->query("truncate table  recharge");
		$Model::$medoo->query("truncate table  sign_in");
		$Model::$medoo->query("truncate table  sms");
		$Model::$medoo->query("truncate table  supplier");
		$Model::$medoo->query("truncate table  user");
		$Model::$medoo->query("truncate table  user_address");
		$Model::$medoo->query("truncate table  user_attach");
		$Model::$medoo->query("truncate table  user_gx");
		$Model::$medoo->query("truncate table  user_im");
		$Model::$medoo->query("truncate table  user_letter");
		$Model::$medoo->query("truncate table  user_msg");
		$Model::$medoo->query("truncate table  withdraw_ye");
		$Model::$medoo->query("truncate table  wx_material");
		$Model::$medoo->query("truncate table  wx_text");
		$Model::$medoo->query("truncate table  payment");
		$Model::$medoo->query("truncate table  run");
		$Model::$medoo->query("truncate table  search");
		$Model::$medoo->query("truncate table  plugin_guess_war");
		$Model::$medoo->query("truncate table  plugin_finger_war");
		$Model::$medoo->query("truncate table  plugin_early_war");
		$Model::$medoo->query("truncate table  wx_openid");
		$Model::$medoo->query("truncate table  transfer");
		$Model::$medoo->query("truncate table  im_team");
		$Model::$medoo->query("truncate table  new_duty");
		$Model::$medoo->query("truncate table  coin_win");
		$Model::$medoo->query("truncate table  drag");
		$Model::$medoo->query("truncate table  drag_num");
		$Model::$medoo->query("truncate table  drag_follow");
		$Model::$medoo->query("truncate table  drag_follow_log");
		$Model::$medoo->query("truncate table  product_collect");
		$Model::$medoo->query("truncate table  shop_collect");
		$Model::$medoo->query("truncate table  product_review");
		$Model::$medoo->query("truncate table  product_review_pic");
		$Model::$medoo->query("truncate table  supplier_complaint");
		$Model::$medoo->query("truncate table  supplier_promotion");
		$Model::$medoo->query("truncate table  agent");
		$Model::$medoo->query("truncate table  feedback");
		$Model::$medoo->query("truncate table  block_recharge");
		$Model::$medoo->query("truncate table  block_withdraw");
		$Model::$medoo->query("truncate table  block_freed");
		$Model::$medoo->query("truncate table  drag_cloud");

		//快递公司
		$Model::$medoo->query("delete from mail_area where cid in (select id from mail where sid!=0)");
		$Model::$medoo->query("delete from mail where sid!=0");
		$Model::$medoo->query("delete from extract where sid!=0");
		$Model::$medoo->query("delete from image where cate in ('supplier','return_good','order_return')");
		$Model::$medoo->query("delete from packet where cdn_sid>0");
		
		
		//定制清空指定表
		$Model::$medoo->query("truncate table vip_order");
		$Model::$medoo->query("truncate table mxq_order");
		$Model::$medoo->query("truncate table gdxq_order");
		$Model::$medoo->query("truncate table run_oc");
		$Model::$medoo->query("truncate table user_fxtj");
		$Model::$medoo->query("truncate table run_oc1");
		
		$Model::$medoo->query("truncate table bbdh");
		$Model::$medoo->query("truncate table hjzj_order");
		$Model::$medoo->query("truncate table run_oc2");
		
		$Model::$medoo->query("truncate table rhgc_tjb");
		
	}


	//清空新闻
	public function news()
	{
		(new \app\model\news)->del_all(['id[>]' => 2]);
	}


	//清空全部商品
	public function product()
	{
		$Model = new \core\lib\Model();
		$id_ar = (new \app\model\product())->lists_all([], 'id');
		(new \app\model\coupon())->up_all(['pid' => $id_ar, 'is_use' => 0], ['is_use' => 2]);
		$Model::$medoo->query("delete from image where cate in ('product')");
		$Model::$medoo->query("truncate table  product");
		$Model::$medoo->query("truncate table  product_attr");
		$Model::$medoo->query("truncate table  product_collect");
		$Model::$medoo->query("truncate table  product_price");
		$Model::$medoo->query("truncate table  product_review");
		$Model::$medoo->query("truncate table  product_review_pic");
		$Model::$medoo->query("truncate table  product_sku");
	}

	//清空全部商户商品
	public function supplier()
	{
		$id_ar = (new \app\model\product())->lists_all(['sid[!]' => 0], 'id');
		//回滚开始
		$model = new \core\lib\Model();
		$redis = new \core\lib\redis();
		$model->action();
		$redis->multi();
		foreach ($id_ar as $one) {
			if ($one) {
				$where['pid'] = $one;
				(new \app\model\coupon())->up_all(['pid' => $one, 'is_use' => 0], ['is_use' => 2]);
				(new \app\model\product_sku())->del_all($where);
				(new \app\model\product_attr())->del_all($where);
				(new \app\model\product_price())->del_all($where);
				$where_pic = (new \app\model\product_review())->find($where, 'id');
				(new \app\model\product_review_pic())->del_all($where_pic);
				(new \app\model\product_review())->del_all($where);
				$res = (new \app\model\product())->del($one);
				empty($res) && error('删除失败', 400);
			}
		}
		$model->run();
		$redis->exec();
	}


	public function admin()
	{
		$redis = new \core\lib\redis();
		$admin_ar = (new \app\model\admin())->lists_all(['username[!]' => ['zhanbang520','zhanbang522']], 'id');
		foreach ($admin_ar as $vo) {
			$rd_name = 'admin:' . $vo;
			$redis->hdel($rd_name);
		}
		(new \app\model\admin())->del_all(['username[!]' => ['zhanbang520','zhanbang522']]);
		(new \app\model\admin())->up_all(['username' => ['zhanbang520','zhanbang522']], ['im'=>'','password'=>'faccb0f195bcea048c20589b29de6db0']);
	}

	public function admin_money()
	{
		(new \app\model\admin_money())->up(1,['money'=>0]);		
		return true;
	}

	public function plugin()
	{
		(new \app\model\plugin())->up_all([], ['is_open' => 0]);
		$iden = (new \app\model\plugin())->lists_all([], ['iden', 'is_open']);
		$redis = new \core\lib\redis();
		foreach ($iden as $vo) {
			$key = 'plugin:' . $vo['iden'];
			$redis->set($key, $vo['is_open']);
		}
		(new \app\model\rating())->up_all([],['flag'=>'']);
	}

	public function menu()
	{
		(new \app\model\menu())->up_all([], ['show' => 0]);
		$where['title'] = [
			'设置',
			'商城',
			'店铺设置',
			'支付设置',
			'页面设置',
			'提现设置',
			'物流',
			'商城首页模板设置',
			'用户',
			'二维码海报模板',
			'消息模板',
			'财务',
			'商品分类',
			'商品管理',
			'商品SKU管理',
			'商品评价',
			'退换货申请',
			'订单管理',
			'会员管理',
			'会员留言',
			'充值',
			'资讯',
			'分类',
			'文章',
			'提现',
			'会员中心模板',
			'后台设置',
			'管理员',
			'角色',
			'日志',
			'店铺设置',
			'购物设置',
			'快递',
			'自提',
			'站内信',
			'商品分类页模板',
			'首页',
			'资金流水',
			'管理',
			'登录页模板',
			'红包发放',
			'促销',
			'模块大市场',
			'支付宝转账',
			'专属转账通道',
			'互转设置',
			'互转记录',
			'我的模块',
			'会员等级',
			'会员商品首页模板',
			'公告',
			'用户协议',
			'用户导入',
			'导入记录',
			'用户反馈',
		];
		(new \app\model\menu())->up_all($where, ['show' => 1]);
	}

	public function config()
	{
		$Model = new \core\lib\Model();
		//$Model::$medoo->query("update config set value='' where iden='ptid'");
		renew_c('ptid');
		$Model::$medoo->query("update config set value=0 where iden='duanxinsl'");
		renew_c('duanxinsl');
		$Model::$medoo->query("update config set value=0 where iden='kqoss'");
		renew_c('kqoss');
		//$Model::$medoo->query("update config set value='' where iden='OSS_wzwjj'");
		renew_c('OSS_wzwjj');
		$Model::$medoo->query("update config set value='' where iden='made'");
		renew_c('made');
	}
}
