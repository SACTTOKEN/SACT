<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: banner接口
 */
namespace app\ctrl\common;

class notify_url
{
    public $order_M;
    public $recharge_M;
    public $payment_M;
    public function __construct(){
        $this->order_M=new \app\model\order();
        $this->payment_M=new \app\model\payment();
        $this->recharge_M=new \app\model\recharge();
    }
    
    public function fill_pay()
    {
        //$xml = file_get_contents('php://input');
		//txt_log($xml,'pay');//检测是否执行callback方法，如果执行，会生成1.txt文件，且文件中的内容就是通知参数
        $wechat=new \extend\full_pay\request();
        $res=$wechat->callback();
        if($res){
            $value['out_trade_no']=$res['out_trade_no'];
            $value['trade_no']=$res['transaction_id'];
            $value['money']=$res['total_fee'];
            $trade_type='中信银行公众号支付';
            if($value['out_trade_no'][0]=='R'){
                $this->order_recharge($value,$trade_type);
            }else{
                $this->order_succeed($value,$trade_type);
            }
            echo 'success';
            exit;
        }
    }

    public function wechat()
    {
        $wechat=new \extend\wechat_pay\notify();
        $res=$wechat->index();
        if($res){
            $value['out_trade_no']=$res['out_trade_no'];
            $value['trade_no']=$res['transaction_id'];
            $value['money']=$res['total_fee'];
            if($res['trade_type']=='APP'){
                $trade_type='微信APP支付';
            }else{
                $trade_type='微信公众号支付';
            }
            if($value['out_trade_no'][0]=='R'){
                $this->order_recharge($value,$trade_type);
            }elseif($value['out_trade_no'][0]=='J'){
                $juhe_S = new \app\service\juhe_recharge();
                $juhe_S -> pay_success($value,$trade_type);
            }else{                
                $this->order_succeed($value,$trade_type);
            }
            echo "<xml><return_code>SUCCESS</return_code><return_msg>OK</return_msg></xml>";
            exit;
        }
    }

    public function alipay_wap()
    {
        $wechat=new \extend\alipay_wap\notify();
        $res=$wechat->index();
        if($res){
            $value['out_trade_no']=$res['out_trade_no'];
            $value['trade_no']=$res['trade_no'];
            $value['money']=$res['total_amount'];
            if($value['out_trade_no'][0]=='R'){
                $this->order_recharge($value,'支付宝手机支付');
            }elseif($value['out_trade_no'][0]=='J'){
                $juhe_S = new \app\service\juhe_recharge();
                $juhe_S -> pay_success($value,'支付宝手机支付');
            }else{
                $this->order_succeed($value,'支付宝手机支付');
            }
            echo "success";
            exit;
        }
    }

    public function alipay_app()
    {
        $wechat=new \extend\alipay_wap\notify('alipay_app');
        $res=$wechat->index();
        if($res){
            $value['out_trade_no']=$res['out_trade_no'];
            $value['trade_no']=$res['trade_no'];
            $value['money']=$res['total_amount'];
            if($value['out_trade_no'][0]=='R'){
                $this->order_recharge($value,'支付宝APP支付');
            }elseif($value['out_trade_no'][0]=='J'){
                $juhe_S = new \app\service\juhe_recharge();
                $juhe_S -> pay_success($value,'支付宝APP支付');      
            }else{
                $this->order_succeed($value,'支付宝APP支付');
            }
            echo "success";
            exit;
        }
    }

    public function alipay_return()
    {
        $out_trade_no=get('out_trade_no');
        if($out_trade_no[0]=='R'){
            $where['oid']=$out_trade_no;
            $id=$this->recharge_M->have($where,'id');
            $web = c('wx_mobile_web');
            header('Location:'.$web.'/pay/paydetails?id='.$id);  
        }else{
            $where['oid']=$out_trade_no;
            $id=$this->order_M->have($where,'id');
            $web = c('wx_mobile_web');
            header('Location:'.$web.'/order/paydetails?id='.$id);  
        }
         
    }

    private function order_recharge($value,$types)
    {
        $where['oid']=$value['out_trade_no'];
        $where['status']=0;
        $order_ar=$this->recharge_M->have($where);
        empty($order_ar) && error('订单不存在',404);
        $where_ar['oid']=$value['out_trade_no'];
        $is_pay=$this->payment_M->is_have($where_ar);
        if($is_pay){
            error('已支付',404);
        }
    
        $this->recharge_M->up($order_ar['id'],['status'=>1,'pay_time'=>time(),'pay'=>$types]); 
        $data['oid']=$value['out_trade_no'];
        $data['trade_no']=$value['trade_no'];
        $data['uid']=$order_ar['uid'];
        $data['money']=$value['money']/100;
        $data['types']=$types;
        $data['cate']='充值';
        $re=$this->payment_M->save($data);
        empty($re) && error('添加支付记录错误',404);
        
        $money_S = new \app\service\money();
        $money_S->plus($order_ar['uid'],$order_ar['money'],$order_ar['cate'],"online_recharge",$order_ar['oid'],$order_ar['uid'],'会员在线充值');
  
    }

    private function order_succeed($value,$types)
    {
        $where['oid']=$value['out_trade_no'];
        $where['is_pay']=0;
        $order_ar=$this->order_M->have($where);
        empty($order_ar) && error('订单不存在',404);
        $where_ar['oid']=$value['out_trade_no'];
        $is_pay=$this->payment_M->is_have($where_ar);
        if($is_pay){
            error('已支付',404);
        }
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();
            $this->order_M->up($order_ar['id'],['status'=>'已支付','is_pay'=>1,'pay_time'=>time(),'pay'=>$types]); 
            $data['oid']=$value['out_trade_no'];
            $data['trade_no']=$value['trade_no'];
            $data['uid']=$order_ar['uid'];
            $data['money']=$value['money']/100;
            $data['types']=$types;
            $data['cate']='订单支付';
            $re=$this->payment_M->save($data);
            empty($re) && error('添加支付记录错误',404);

            //消费红包发放BEGIN 无需领到coupon中，满足条件直接奖励
            if(plugin_is_open('xfhb')){
                $where_c['oid'] = $value['out_trade_no'];
                $where_c['uid'] = $order_ar['uid'];
                $coupon_M = new \app\model\coupon();
                $is_have_coupon = $coupon_M->is_have($where_c); //防反复领取
                if(!$is_have_coupon){
                    $coupon_S = new \app\service\coupon();
                    $coupon_S -> packet_xf_pj($order_ar['uid'],'xf',$value['out_trade_no']);
                }    

            }
            //消费红包发放END
        
            $cd=(new \app\service\order())->split($order_ar['id']);
            if(!$cd){
                error('拆单错误',404);
            }
        $Model->run();
        $redis->exec();
    }

    
    public function is_pay()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id');
        $where['is_pay']=1;
        $where['id']=$id;
        $res=$this->order_M->is_have($where);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
        exit;
    }

    
    public function is_recharge_pay()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id');
        $where['status']=1;
        $where['id']=$id;
        $res=$this->recharge_M->is_have($where);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
        exit;
    }


   /**接受话费异步通知*/   
    public function juhe_hf(){
        $appkey = c('juhe_hf_appkey');
        $sporder_id = addslashes($_POST['sporder_id']); //聚合订单号
        $orderid = addslashes($_POST['orderid']); //商户的单号
        $sta = addslashes($_POST['sta']); //充值状态
        $sign = addslashes($_POST['sign']); //校验值
         
        $local_sign = md5($appkey.$sporder_id.$orderid); //本地sign校验值
         
        if ($local_sign == $sign) {
            if ($sta == '1') {
                return true;
            } elseif ($sta =='9') {
                $juhe_recharge_M = new \app\model\juhe_recharge();

                $model = new \core\lib\Model();
                $redis = new \core\lib\redis();  
                $model->action();
                $redis->multi();

                $where['oid'] = $orderid;
                $where['juhe_oid'] = $sporder_id;
                $ar = $juhe_recharge_M ->find($where);
                if(isset($ar['id'])){
                    $juhe_recharge_M -> up($ar['id'],['game_state'=>9]); //撤销 退钱
                    $this->money_S->plus($ar['uid'],$ar['money'],'money','juhe_hf_return',$ar['oid'],$ar['id'],'话费充值失败退款'); //记录资金流水
                }else{
                    return false;
                }
                
                $model->run();
                $redis->exec();
            }
        }

    } 

   /**接受流量充值异步通知*/   
    public function juhe_ll(){
        $appkey = c('juhe_ll_appkey');     
        $sporder_id = addslashes($_POST['sporder_id']); //聚合订单号
        $orderid = addslashes($_POST['orderid']); //商户的单号
        $sta = addslashes($_POST['sta']); //充值状态
        $sign = addslashes($_POST['sign']); //校验值
         
        $local_sign = md5($appkey.$sporder_id.$orderid); //本地sign校验值
         
        if ($local_sign == $sign) {
            if ($sta == '1') {
                return true;
            } elseif ($sta =='9') {
                $juhe_recharge_M = new \app\model\juhe_recharge();
                $model = new \core\lib\Model();
                $redis = new \core\lib\redis();  
                $model->action();
                $redis->multi();

                $where['oid'] = $orderid;
                $where['juhe_oid'] = $sporder_id;
                $ar = $juhe_recharge_M ->find($where);
                if(isset($ar['id'])){
                    $juhe_recharge_M -> up($ar['id'],['game_state'=>9]); //撤销 退钱
                    $this->money_S->plus($ar['uid'],$ar['money'],'money','juhe_ll_return',$ar['oid'],$ar['id'],'流量充值失败退款'); //记录资金流水
                }else{
                    return false;
                }

                $model->run();
                $redis->exec();
            }
        }

    } 

   /**接受加油卡充值异步通知*/   
    public function juhe_yk(){
        $appkey = c('juhe_yk_appkey');  
        $sporder_id = addslashes($_POST['sporder_id']); //聚合订单号
        $orderid = addslashes($_POST['orderid']); //商户的单号
        $sta = addslashes($_POST['sta']); //充值状态
        $sign = addslashes($_POST['sign']); //校验值
         
        $local_sign = md5($appkey.$sporder_id.$orderid); //本地sign校验值
         
        if ($local_sign == $sign) {
            if ($sta == '1') {
                return true;
            } elseif ($sta =='9') {
                $juhe_recharge_M = new \app\model\juhe_recharge();
                $model = new \core\lib\Model();
                $redis = new \core\lib\redis();  
                $model->action();
                $redis->multi();

                $where['oid'] = $orderid;
                $where['juhe_oid'] = $sporder_id;
                $ar = $juhe_recharge_M ->find($where);
                if(isset($ar['id'])){
                    $juhe_recharge_M -> up($ar['id'],['game_state'=>9]); //撤销 退钱
                    $this->money_S->plus($ar['uid'],$ar['money'],'money','juhe_yk_return',$ar['oid'],$ar['id'],'油卡充值失败退款'); //记录资金流水
                }else{
                    return false;
                }

                $model->run();
                $redis->exec();
            }
        }

    } 

    public function admin_money()
    {
        $data=post(['oid','money','types']);
        ksort($data);
		$sign_url='';
		foreach ($data as $key => $val)
		{
			$sign_url.=$key.'='.$val.'&';
		}
        $sign_url.='key='.c('admin_key');
        $sign = md5('@'.$sign_url.'@');
        if($sign!=post('sign')){
            error('签名失败',404);
        }
        $admin_money_M=new \app\model\admin_money();
        $money=$admin_money_M->find(1,'money');
        if($data['types']==2){
            if($data['money']>$money){
                error('金额不足',404);
            }
        }
        if($data['types']==1){
            $new_money=$money+$data['money'];
        }else{
            $new_money=$money-$data['money'];
        }
        $admin_money_M->up(1,['money'=>$new_money]);
        $types=($data['types']==1)?'+':'-';
        admin_log('OA充值订单号:'.$data['oid'].'当前余额:'.$new_money,$types.$data['money']);
        $res['before_balance']=$money;
        $res['after_balance']=$new_money;
        return $res;
    }






}
