<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-03 15:16:08
 * Desc: 快递公司控制器
 */
namespace app\ctrl\admin;

use app\model\mail as MailModel;
use app\ctrl\admin\BaseController;
use app\validate\MailValidate;

class mail extends BaseController{

	public $mail_M;
	public function __initialize(){
		$this->mail_M = new MailModel();
	}
	
	/*修改*/
	public function edit()
	{
		$mail_ar=$this->mail_M->have(['sid'=>0]);
		if(empty($mail_ar)){
			$this->mail_M->save(['sid'=>0,'title'=>'快递公司','title_en'=>'快递编码']);
		}
		$res=$this->mail_M->have(['sid'=>0]);
		$res['first_weight'] = sprintf("%.2f",$res['first_weight']);
		$res['continued_weight'] = sprintf("%.2f",$res['continued_weight']);
		$res['free_post'] = sprintf("%.2f",$res['free_post']);
		$res['kdn_is_open'] = plugin_is_open('kdlwnjk');
		$res['kdn_id'] = c('kdn_id');
		empty($res) && error('数据不存在',404); 
		return $res;
	}

	/*按sid修改*/
	public function saveedit_by_sid()
	{
		$id=$this->mail_M->have(['sid'=>0],'id');
		empty($id) && error('数据不存在',404);
		(new MailValidate())->goCheck('scene_saveedit_by_sid');
		$data = post(['title','title_en','links','sort','first_weight','continued_weight','free_post','is_free_post','kdn_sender_name','kdn_sender_mobile','kdn_customer_name','kdn_customer_pwd','kdn_shipper','kdn_shipper_code','kdn_sender_address','kdn_sender_province','kdn_sender_city','kdn_sender_area','is_kdl']);
		$res=$this->mail_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改物流',$id);    
 		return $res; 
	}

}