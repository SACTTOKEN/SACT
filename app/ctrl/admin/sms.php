<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-25
 * Desc: 短信控制器 
 */
namespace app\ctrl\admin;

use app\model\sms as SmsModel;
use app\ctrl\admin\BaseController;
use app\validate\SmsValidate;


class sms extends BaseController{
	
	public $smsM;
	public function __initialize(){
		$this->smsM = new SmsModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');	
    	(new SmsValidate())->goCheck('scene_find');
    	$data = $this->smsM->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

   
	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];

		$uid = post('uid','');
		$tel = post('tel','');

		if($uid){
			$where['uid'] = $uid;
		}
		if($tel){
			$where['tel[~]'] = $tel;
		}
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->smsM->lists($page,$page_size,$where);
		$count = $this->smsM->new_count($where);

		foreach ($data as &$value) {
			$value['ip_address']=ip_address($value['ip']);
			if($value['uid']){
			$users=user_info($value['uid']);
			$value['uid']=$users['username'].'【'.$users['nickname'].'】';
			}
		}

        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}



//================= 以上是基础方法 ==================
}