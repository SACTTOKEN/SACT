<?php
namespace app\service;
class wechat_redpack{  
    //微信现金红包
    public function wx_redpack($openid,$money,$real_money,$oid,$uid,$balance_type){
        //后台支付设置里 加 微信提现wechat_withdraw 始终关闭，从里面读配置
        $pay_M = new \app\model\pay();
        $pay = $pay_M->have(['iden'=>'wechat_withdraw']);
        $mch_id  = $pay['username'];   //商户号;  
        $mch_secret = $pay['key'];    //商户密钥;       
        $appid = $pay['app_id']; //'wxe392a6831e86dd02'; 
        $appsecret = $pay['app_secret'];   //'1f8c40fb70e9ce4bbaebc8fa61100a0d';// 
        $sender = $pay['content'];

        $obj = array();
        $obj['wxappid']         = $appid;
        $obj['mch_id']          = $mch_id;
        $obj['mch_billno']      = $oid; //$mch_id.date('YmdHis').rand(1000, 9999);
        $obj['client_ip']       = $_SERVER['REMOTE_ADDR'];
        $obj['re_openid']       = $openid;
        $obj['total_amount']    = $real_money;
        $obj['total_num']       = 1;
        $obj['nick_name']       = $sender;
        $obj['send_name']       = $sender;
        $obj['wishing']         = "恭喜发财";
        $obj['act_name']        = "恭喜发财";
        $obj['remark']          = "恭喜发财";
        
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $wxHongBaoHelper = new \extend\wechat_cash\wx_redpack();
        $data = $wxHongBaoHelper->wxpay($url, $obj, $mch_secret);
        $res = $wxHongBaoHelper->xmlToArray($data);
        
            if($res['return_code']=='SUCCESS' && $res['result_code']!='FAIL'){
                $redpack_log_M = new \app\model\redpack_log();
                $data_log['uid'] = $uid;
                $data_log['money'] = $money;
                $data_log['real_money'] = $res['total_amount']/100;
                $data_log['balance_type'] = $balance_type;  //money
                $data_log['oid'] = $res['mch_billno'];
                $data_log['return_oid'] = $res['send_listid'];
                $data_log['pay_way'] = '微信现金红包';
                $data_log['openid'] = $res['re_openid'];
                $data_log['ip'] = ip();
                $redpack_log_M->save($data_log);
                return true;
            }else{      
                if( strpos($res['err_code_des'],'openid字段必填')>0 ){
                    error('请在微信平台提现',400);
                }else{
                    error($res['err_code_des'],400);
                }
            }

    }

    
    //企业付款给用户 $money 单位：元   $real_money 单位：分
    public function qy_redpack($openid,$money,$real_money,$oid,$uid,$balance_type){
        $pay_M = new \app\model\pay();
        $pay = $pay_M->have(['iden'=>'wechat_withdraw']);
        $mch_id  = $pay['username'];   //商户号;  
        $mch_secret = $pay['key'];    //商户密钥;       
        $appid = $pay['app_id']; //'wxe392a6831e86dd02'; 
        $appsecret = $pay['app_secret'];   //'1f8c40fb70e9ce4bbaebc8fa61100a0d';// 
        $sender = $pay['content'];

        $obj = array();
        $obj['openid']           = $openid;
        $obj['amount']           = $real_money;
        $obj['desc']             = "企业付款";
        $obj['mch_appid']        = $appid;
        $obj['mchid']            = $mch_id;
        $obj['partner_trade_no'] = $oid; //MCHID.date('YmdHis').rand(1000, 9999);
        $obj['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $obj['check_name']       = "NO_CHECK";
        $obj['re_user_name']     = $sender;

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $wxHongBaoHelper = new \extend\wechat_cash\wx_redpack();
        $data = $wxHongBaoHelper->wxpay($url, $obj, $mch_secret);
        $res = $wxHongBaoHelper->xmlToArray($data);
                    
        if($res['return_code']=='SUCCESS' && $res['result_code']!='FAIL'){
            $redpack_log_M = new \app\model\redpack_log();
            $data_log['uid'] = $uid;
            $data_log['money'] = $money;
            $data_log['real_money'] = $real_money/100;
            $data_log['balance_type'] = $balance_type;  //money
            $data_log['oid'] = $res['partner_trade_no'];
            $data_log['return_oid'] = $res['payment_no'];
            $data_log['pay_way'] = '微信企业付款';
            $data_log['openid'] = $openid;
            $data_log['ip'] = ip();
            $redpack_log_M->save($data_log);
            return true;
        }else{
            if( strpos($res['err_code_des'],'openid字段必填')>0 ){
                error('请在微信平台提现',400);
            }else{
                error($res['err_code_des'],400);
            }
        }
    }

       

} //END

/**
红包发送成功返回示例
[return_code] => SUCCESS
[return_msg] => 发放成功
[result_code] => SUCCESS
[err_code] => SUCCESS
[err_code_des] => 发放成功
[mch_billno] => W20190820105236798733
[mch_id] => 1321942401
[wxappid] => wx2a219c2aefa8e0a9
[re_openid] => octLbwimNQup7H8NbhFVCQA1jLJI
[total_amount] => 100
[send_listid] => 1000041701201908203000055233482


付款成功返回示例
'return_code' => string 'SUCCESS' (length=7)
'return_msg' => 
array (size=0)
empty
'mch_appid' => string 'wx2a219c2aefa8e0a9' (length=18)
'mchid' => string '1321942401' (length=10)
'nonce_str' => string 'DOGbj2i5rYBP4gXLkOsnynmiCJTcm1qk' (length=32)
'result_code' => string 'SUCCESS' (length=7)
'partner_trade_no' => string 'W20190821030821800844' (length=21)
'payment_no' => string '10100115621061908210036101077278' (length=32)
'payment_time' => string '2019-08-21 15:08:22' (length=19)
*/