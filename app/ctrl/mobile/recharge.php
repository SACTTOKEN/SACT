<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 17:02:46
 * Desc: 充值
 */
namespace app\ctrl\mobile;

use app\Validate\IDMustBeRequire;
use app\model\user as UserModel;
use app\ctrl\mobile\BaseController;

class recharge extends BaseController
{
	public $user_M;
	
	public function __initialize(){
		$this->user_M = new UserModel();	
	}

    /*充值 pay充值方式汉字*/
    public function my_recharge(){
        $uid = $GLOBALS['user']['id'];
        $data = post(['uid','money','cate','types','pay','status','admin_id','remark']);
        if($uid != $data['uid']){
            error('只能查看自己的记录',400);
        }

        (new \app\validate\RechargeValidate())->goCheck('scene_add');
        $recharge_M = new \app\model\recharge();
        $res = $recharge_M->save($data);
        $oid = date('Ymd').rand(100,999).$res; //生成订单号
        $up['oid'] = $oid;
        $res2 = $recharge_M->up($res,$up);

        empty($res2) && error('充值失败',400);    
        return $res;
    }


    /*充值记录*/
    public function recharge_log(){
        (new \app\validate\AllsearchValidate())->goCheck();
        $uid = $GLOBALS['user']['id'];
        $page = post("page",1);
        $page_size = post("page_size",10);
        $recharge_M = new \app\model\recharge();
        $data = $recharge_M->lists($uid,$page,$page_size);
     
        $res['data'] = $data;

        // cs($recharge_M->log());
        // exit();
        return $res; 
    }
    



}






