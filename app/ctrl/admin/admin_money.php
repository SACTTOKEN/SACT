<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 示例
 */

namespace app\ctrl\admin;

use app\validate\IDMustBeRequire;

class admin_money extends BaseController
{
    public $admin_money_M;
	public function __initialize()
	{
        $this->admin_money_M=new \app\model\admin_money();
    }
    
    //当前余额
    public function info()
    {
        return $this->admin_money_M->find(1,'money');
    }


    //开通插件
    public function open()
    {
        $id=post('id');
        (new IDMustBeRequire())->goCheck();
        $plugin_M=new \app\model\plugin();
        $plugin_ar=$plugin_M->find($id);
        empty($plugin_ar) && error('插件不存在',404);
        if($plugin_ar['is_open']==1){
            error('插件已购买',404);
        }

        $admin_money_M=new \app\model\admin_money();
        $money=$admin_money_M->find(1,'money');
        if($plugin_ar['price']>$money){
            error('金额不足',404);
        }
        $new_money=$money-$plugin_ar['price'];
        $admin_money_M->up(1,['money'=>$new_money]);

        
		$data['is_open'] = 1;
		$res=$plugin_M->up($id,$data);
		$redis = new \core\lib\redis();
		$key = 'plugin:'.$plugin_ar['iden'];
		$redis->set($key,$data['is_open']);
        

        admin_log('OA购买插件:'.$plugin_ar['title'].'当前余额:'.$new_money,'-'.$plugin_ar['price']);
        return '购买成功';
    }
    
    //剩余短信条数
    public function sms()
    {
        $number=(new \app\model\sms())->new_count();
        return c('duanxinsl')-$number;
    }

    //短信套餐
    public function sms_package()
    {
        return [
            [
                'id'=>1,
                'title'=>'套餐一',
                'money'=>'1000',
                'number'=>'10000',
            ],
            [
                'id'=>2,
                'title'=>'套餐二',
                'money'=>'3000',
                'number'=>'50000',
            ],
            [
                'id'=>3,
                'title'=>'套餐三',
                'money'=>'5000',
                'number'=>'100000',
            ],
        ];
    }

    //短信充值
    public function sms_recharge()
    {
        $id=post('id');
        (new IDMustBeRequire())->goCheck();
        $sms_ar_all=$this->sms_package();
        $sms_ar=$sms_ar_all[$id-1];
        empty($sms_ar) && error('套餐不存在',404);

        $admin_money_M=new \app\model\admin_money();
        $money=$admin_money_M->find(1,'money');
        if($sms_ar['money']>$money){
            error('金额不足',404);
        }
        $new_money=$money-$sms_ar['money'];
        $admin_money_M->up(1,['money'=>$new_money]);

        //短信
        $config_M=new \app\model\config();
        $number=$config_M->find('duanxinsl');
        $new_number=$number+$sms_ar['number'];
        $config_M->up('duanxinsl',['value'=>$new_number]);        
        admin_log('OA充值短信:'.$sms_ar['title'].'当前余额:'.$new_money.',当前条数:'.renew_c('duanxinsl'),'-'.$sms_ar['money']);
        return '充值成功';
    }




}
