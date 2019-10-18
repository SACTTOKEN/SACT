<?php

/**
 * Created by yaaaaa_god
 * User: god
 * Date: 2019-01-03
 * Desc: 消息模板库(所有消息模板集合,放计划任务时刷，暂定一分钟一刷)
 */

namespace app\service;

class sms
{
    public $redis;
    public $client;
    public $app_show = 0;
    public $wx_show = 0;
    public $letter_M;
    public $wx_sms_M;
    public $wechatObj;
    public $money_M;
    public $order_product_M;
    public $reward_M;
    public $order_M;
    public function  __construct()
    {
        $this->wx_sms_M = new \app\model\wx_sms();
        $this->user_M = new \app\model\user();
        $this->money_M = new \app\model\money();
        $this->order_M = new \app\model\order();
        $this->redis = new \core\lib\redis();
        $this->order_product_M = new \app\model\order_product();
        $this->reward_M = new \app\model\reward();
        if (plugin_is_open('appbbxt') && !empty(c('appKey')) && !empty(c('master'))) {
            require_once(IMOOC . 'extend/vendor/jpush/jpush/autoload.php');
            $this->client = new \JPush\Client(c('appKey'), c('master'));
            $this->app_show = 1;
        }
        $this->letter_M = new \app\model\user_letter();
        if (plugin_is_open('wxgzhbb')) { //微信公众号版本
            $this->wechatObj = new \app\service\wechat();
            $this->wx_show = 1;
        }
    }



    /**
     * 模板消息 {{first.DATA}} 发货日期：{{keyword1.DATA}} 到货时间：预计{{keyword2.DATA}}{{remark.DATA}}   
     * $ar参数key值：[user_appid,$sms_id,$head_ar,$body_ar,$url,$remark]
     * sms_id:wx_sms表的id, head_ar:模板消息头部要替换的数组，$body_ar:模板消息体要替换的数组
     */
    public function notice($ar = [])
    {
        $url = c('wx_mobile_web') . $ar['url'];
        $rs = $this->wx_sms_M->have(['title'=>$ar['sms_id']]);


        if (empty($rs)) {
            return;
        }
        $head_html = $rs['content'];
        $head_op=$rs['op'];
        $head_op=explode("@@",$head_op);
        foreach($head_op as $key=>$vo){
            if($vo){
                $head_html=str_replace($vo,$ar['head_ar'][$key],$head_html);
            }
        }

        $first = $head_html;
        $first  = strip_tags($first);
        //微信
      	$openid=$this->user_M->find($ar['user']['id'],'openid');
        if ($this->wx_show && $rs['wx_show'] == 1 && $openid) {

            $this->wechatObj->wlw_wxnotice($openid, $rs['templet_id'], $url, $first, $ar['body_ar'], $rs['bottom']);  //echo "<br>发模板消息中。。。。";
        }

        //站内信
        if ($rs['web_show'] == 1) {
            $data['uid'] = $ar['user']['id'];
            $data['title'] = $rs['bottom'];
            $data['content'] = $first;
            $data['piclink'] = $ar['piclink'];
            $data['links'] = $url;
            $this->letter_M->save($data);
        }

        //APP推送
        if ($rs['app_show'] == 1 && $this->app_show && isset($ar['user']['aurora_id']) && !empty($ar['user']['aurora_id'])) {
            $push_payload = $this->client->push()
                ->setPlatform('all')
                ->addRegistrationId($ar['user']['aurora_id'])
                ->setNotificationAlert($first)
                ->iosNotification($first, array(
                    'extras' => array('url' => $url),
                ))
                ->androidNotification($first, array(
                    'extras' => array('url' => $url),
                ))
                ->options(array(
                    'apns_production' => true,
                ));
            $a=$push_payload->send();
        }

        //短信
        if ($rs['tel_show'] == 1 && isset($ar['user']['tel']) && !empty($ar['user']['tel'])) {
            $msms_S = new \app\service\msms();
            $code = '您有一条新的通知';
            $btdx = plugin_is_open('btdx');
            $gjdx = plugin_is_open('gjdx'); //是否开放国际短信
            $quhao = isset($ar['quhao']) ? $ar['quhao'] : '86';
            if (($btdx == 1 || $gjdx == 1) && $quhao == '86') {
                $msms_S->btdx($code, $ar['user']['tel'], 'c2c_sms_templateId');
            } elseif ($quhao != '86' && $gjdx == 1) {
                $msms_S->gjdx($code, $ar['user']['tel'], $quhao, 'c2c_sms_templateId');
            }
        }
    }



  
    /*消息redis队列处理 mbxx，处理一个删除一个*/
    public function index()
    {
        $this->redis_sms();
        $this->money_sms();
    }

    public function redis_sms($level = 0)
    {
        $test_ar = $this->redis->lrange('mbxx');
        if (isset($test_ar[0])) {
            $send_json = $this->redis->rpop('mbxx');
            $send_ar = json_decode($send_json, true);
            if ($send_ar['action']) {
                $action = $send_ar['action'];
                $this->$action($send_ar);
            }
            if ($level < 100) {
                $this->redis_sms($level++); //5秒钟处理一百个
            }
        }
    }

    public function money_sms()
    {
        $where['is_sms']=0;
        $where['LIMIT']=[0,1000];
        $money_ar=$this->money_M->lists_all($where);
        $coin=['coin','coin_storage','integrity','USDT','BTC','ETH','LTC','BCH'];
        foreach($money_ar as $vo){
            $this->money_M->up($vo['id'],['is_sms'=>1]);
            $ly_name='';
            $my_name='';
            $reward_res=$this->reward_M->is_have(['iden'=>$vo['iden'],'show'=>1,'is_sms'=>1]);
            $users = user_info($vo['uid']);
            if (empty($users)) {
                continue;
            }
            if(empty($reward_res)){
                continue;
            }
            if(in_array($vo['cate'], $coin)){
                $money=$vo['money'];
                $url='/coin/coinhistory?iden='.$vo['cate'];
            }else{
                $money=sprintf("%.2f",$vo['money']);
                $url='/pay/income?iden='.$vo['cate'];
            }
            $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
            $piclink= $users['avatar'];
            if($vo['ly_id'] && $vo['ly_id']==$vo['uid']){
            $ly_name='自己';
            }elseif($vo['ly_id'] && $vo['ly_id']!=$vo['uid']){
            $ly_users = user_info($vo['ly_id']);
            $ly_name = $ly_users['nickname'] ? $ly_users['nickname'] : $ly_users['username'];
            $piclink= $ly_users['avatar'];
            }
            $ly_name=$ly_name?$ly_name:'无';
            if($vo['types']==1){
                $ar['sms_id'] ='金额加';    
                $moeny='加'.$money.find_reward_redis($vo['cate']);
            }else{
                $ar['sms_id'] ='金额减';    
                $moeny='减'.$money.find_reward_redis($vo['cate']);
            } 
            $order_product_ar=$this->order_product_M->have(['oid'=>$vo['oid']],'piclink');
            if(!empty($order_product_ar)){
                $piclink=$order_product_ar;
            }
            $body_ar=($vo['oid']!='无')?$vo['oid']:($ly_name?$ly_name:$my_name);
            $ar['head_ar'] = [$my_name,$vo['style'],$moeny,$ly_name,$vo['oid'],$vo['remark']];                            
            $ar['body_ar'] = [$moeny, date('Y-m-d H:i:s')];      
            $ar['user'] = $users;                                   
            $ar['url'] = $url;                       
            $ar['piclink'] = $piclink;                      
            $this->notice($ar);
        }
    }

    //测试 查看最后一个值
    public function last()
    {
        $test_ar = $this->redis->lrange('mbxx');
        $send_json = $test_ar[0];
        $send_ar = json_decode($send_json, true);
        cs($send_ar, 1);
    }

    //测试 不写入队列直接发
    public function cs()
    {
        /* $redis = new \core\lib\redis(); 
        $params_ar['xid'] = 123;
        $params_ar['action'] = 'pay_order';
        $params = json_encode($params_ar,JSON_UNESCAPED_UNICODE);
        $redis->lpush('mbxx', $params); */

        $params['xid']=123;     
        $action='pay_order';
        $this->$action($params);
        cs($this->wx_sms_M->log());
    }

}
