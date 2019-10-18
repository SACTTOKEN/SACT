<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 17:02:46
 * Desc: 提现控
 */
namespace app\ctrl\mobile;

use app\Validate\IDMustBeRequire;

use app\model\user as UserModel;

use app\ctrl\mobile\BaseController;

class withdraw extends BaseController
{
	public $user_M;
	
	public function __initialize(){
		$this->user_M = new UserModel();	
	}


    /*申请提现 pay(微信,支付宝) cate(金额类型 余额money  积分integral  佣金amount)*/
    public function my_withdraw(){
        $uid = $GLOBALS['user']['id'];       
        $data = post(['money','cate','pay','remark']);
        $data['uid'] = $uid;

        $cate_all = ['money'=>'余额','integral'=>'积分','amonut'=>'佣金'];
        $cate_type = ['money','integral','amonut'];

        !in_array($data['cate'], $cate_type) && error('cate参数错误',400);
        $cate_name = $cate_all[$data['cate']];
        
        $money = $this->user_M ->find($uid,$data['cate']);
        if($data['money'] > $money){
            error($cate_name."不足",400); 
        }
       
        $withdraw_M = new \app\model\withdraw();
        $res = $withdraw_M->save($data);

        $oid = date('Ymd').rand(100,999).$res;

        $up['oid'] = $oid;
        $res2 = $withdraw_M->up($res,$up);

        empty($res) && error('添加失败',400); 
        return $res;
    }


    /*提现记录*/
    public function withdraw_log(){
        (new \app\validate\AllsearchValidate())->goCheck();
        $uid = $GLOBALS['user']['id'];       
        $page = post("page",1);
        $page_size = post("page_size",10);
        $withdraw_M = new \app\model\withdraw();
        $data = $withdraw_M->lists($uid,$page,$page_size);
        $res['data'] = $data;
        return $res; 
    }



}






