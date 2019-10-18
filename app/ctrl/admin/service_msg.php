<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-26 15:56:55
 * Desc: 客服消息(48小时有互动的客户) 
 */

namespace app\ctrl\admin;

use app\model\service_msg as serviceMsgModel;
use app\validate\IDMustBeRequire;
use app\validate\ServiceMsgValidate;
class service_msg extends BaseController{
	
	public $sm_M;
	public function __initialize(){
		$this->sm_M = new serviceMsgModel();		
	}


	//添加
	public function saveadd(){
		(new ServiceMsgValidate())->goCheck();
		$wx_S = new \app\service\wechat();
		$rating = post('rating');
		$msg = post('msg');

		$rating_M = new \app\model\rating();
		$rating_cn = $rating_M->find($rating,'title');
		empty($rating_cn) && error('该等级不存在',400);

		$user_M = new \app\model\user();
		$where['rating'] = $rating;
		$ar = $user_M -> lists_all($where);

		$success_num = 0;
		$fail_num = 0;
		foreach($ar as $one){
 			$res = $wx_S->service_msg($one['openid'],$msg); // ['errcode'=>0,'errmsg'=>'ok']
 			if($res['errcode']==0){
 				$success_num++;
 			}else{
 				$fail_num++;
 			}
		}

		$data['admin_id'] = $GLOBALS['admin']['id'];
		$data['rating'] = $rating;
		$data['rating_cn'] = $rating_cn;
		$data['success_num'] = $success_num;
		$data['fail_num'] = $fail_num;
		$data['msg'] = $msg;
		$res = $this->sm_M->save($data);
		empty($res) && error('发送失败',400);
		admin_log('发送客服消息',$res); 

		return $res;
	}


	//发送记录列表
	public function lists(){
		(new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
		$page_size = post("page_size",10);	
		$where = [];
		$rating = post('rating');
		if($rating){
			$where['rating'] = $rating;
		}
		$data=$this->sm_M->lists($page,$page_size,$where);
		$count = $this->sm_M->new_count($where);
		$res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res;
	}





   









	


}