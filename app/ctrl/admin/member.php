<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-08 13:43:56
 * Desc: 会员中心配置
 */
namespace app\ctrl\admin;

use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;
use core\lib\redis;
use app\validate\RatingValidate;

class member extends BaseController{
	public $redis;
	public function __initialize(){
		$this->redis = new redis();
	}

	/*配置*/
	public function lists()
	{
		$data['top'] = [
			'title'=>'头部',
			'data'=>[
				'kf'=>'我的客服',		
				'sz'=>'设置',
				'sms'=>'站内信',
                'tjr' => '推荐人',
				'super'=>'开通商城会员',
				'hym'=>'会员码',
			]
		];

		$data['money'] =[
			'title'=>'资金',
			'data'=>[
             	'yr' => find_reward_redis('money'),
				'jf' => find_reward_redis('integral'),
				'yj' => find_reward_redis('amount'),			
				'weight' => find_reward_redis('weight'),
				'gold' => find_reward_redis('gold'),
			]
		];
		
		if(plugin_is_open('shbxt')==1){
			$data['money']['data']['supply'] =	find_reward_redis('supply');
		}
		if(plugin_is_open('xnbkj')==1){
			$data['money']['data']['xlb'] =	'虚拟币';
			$data['money']['data']['xlbhd'] =	find_reward_redis('coin');
			$data['money']['data']['xlbcc'] =	 find_reward_redis('coin_storage');
			$data['money']['data']['integrity'] =	find_reward_redis('integrity');
		}
		

		$data['my_order'] = [
			'title'=>'我的订单',
			'data'=>[
				'dfk'=>'待付款',
				'dfh'=>'待发货',
				'dsh'=>'待收货',
				'dpj'=>'待评价',
				'sh' =>'售后',
				]
		];

		$data['tool'] = [
			'title' => '会员工具',
			'data'  => [
			       'wdhb' =>'我的红包',
			       'scsp' =>'我的收藏',
			       'scdp' =>'资金互转',
			       'tsjy' =>'投诉建议',
				]

		];

		if(plugin_is_open('shbxt')==1){
			$data['tool']['data']['shsz'] = '申请商户';
		}
		if(plugin_is_open('gbfx')==1){
			$data['tool']['data']['shdd'] =  '申请代理';
		}
		if(plugin_is_open('zyfh')==1){
			$data['tool']['data']['wzy'] ='微展业';
		}
		if(plugin_is_open('jfyx')==1){
			$data['tool']['data']['jfsc'] ='积分商城';
		}

		$data['new_power']['title']='新人特权';
		if(plugin_is_open('sqhb')==1){
			$data['new_power']['data']['hbrw'] = [
					'title'	=>'新手任务',
					'title_plus'=>'新手任务副标题'
			];
		}

		if(plugin_is_open('ccn')==1){
			$data['new_power']['data']['agccn'] = [
					'title'	=>'A股猜猜乐',
					'title_plus'=>'A股猜猜乐副标题'
			];
		}

		if(plugin_is_open('zqqd')==1){
			$data['new_power']['data']['zqqd'] = [
					'title'	=>'早起签到',
					'title_plus'=>'早起签到副标题'
			];
		}


		if(plugin_is_open('cq')==1){
			$data['new_power']['data']['jfcq'] = [
					'title'	=>'积分猜拳',
					'title_plus'=>'积分猜拳副标题'
			];
		}


		$data['m_data'] = [
			'title' => '我的市场',
			'data'=>[
				'm_1' =>'我的好友',
				'm_2' =>'团队好友',
				'm_3' =>'团队业绩',
				'm_4'=>'累计收益',
				]
		];


		if(plugin_is_open('xnbkj')==1){
			$data['star'] = ['title' => '区块链星球'];
		}

		$data['recommend'] = [
			'title' => '为你推荐'
		];

        return $data; 
	}

	/*按会员等级id修改*/
	public function saveedit()
	{	
		$rating_M = new \app\model\rating();	
		$id = post('id');
    	$data = post(['piclink','flag']);
    	$all = post('all');
    	if($all==1){		
		$res = $rating_M->up_all($data);   //修改所有的等级为相同的设置
    	}else{
			$res = $rating_M->up($id,$data);
    	}   			
		empty($res) && error('修改失败',404);
		admin_log('修改商城等级',$id);    
 		return $res;
	}

	public function find(){

		(new IDMustBeRequire())->goCheck();
		$rating_M = new \app\model\rating();
		$id = post('id');
		$res = $rating_M->find($id);
		empty($res) && error('查找失败',404);
		return $res;
	}
	
		
}