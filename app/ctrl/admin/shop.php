<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 管理员角色类
 */
namespace app\ctrl\admin;
use app\validate\IDMustBeRequire;

class shop extends PublicController{

	public $user_attach_M;
	public function __initialize(){
		$this->user_attach_M = new \app\model\user_attach();
    }
    
	public function edit()
	{
        (new IDMustBeRequire())->goCheck();
        $id=post('id');
		$data=$this->user_attach_M->find($id,['uid','shop_wechat','shop_recommend','shop_title','shop_logo','shop_tel','shop_province','shop_city','shop_area','shop_town','shop_address','shop_cate','shop_longitude','shop_latitude','shop_fee','shop_referrer']);
        return $data; 
    }
    
    public function saveedit()
    {
        (new IDMustBeRequire())->goCheck();
		(new \app\validate\UserAttachValidate())->goCheck('supplier');
        $id=post('id');
        $data=post(['shop_title','shop_wechat','shop_recommend','shop_logo','shop_tel','shop_province','shop_city','shop_area','shop_town','shop_address','shop_cate','shop_longitude','shop_latitude','shop_fee','shop_referrer']);
        $res=$this->user_attach_M->up($id,$data);
        empty($res) && error("修改失败",404);
		admin_log('修改商户信息',$id); 
        return $res; 
    }

    
}