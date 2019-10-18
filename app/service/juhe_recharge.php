<?php
//----------------------------------
// 聚合数据-充值服务
//----------------------------------


namespace app\service;

class juhe_recharge {
    /*@ar[out_trade_no,trade_no,money]
      @type 支付方式*/
    public function pay_success($ar,$type){
        $oid = $ar['out_trade_no'];
        $ar = $this->juhe_M->have(['oid'=>$oid]);
            $model = new \core\lib\Model();
            $redis = new \core\lib\redis();  
            $model->action();
            $redis->multi();
        $data['is_pay'] = 1; 
        $data['game_state'] = 1;  
        $data['pay'] = $type;

        $this->juhe_M->up($id,$data);
        switch ($ar['types']) {
            case '话费':
                $huafei_S = new \app\service\juhe_huafei();
                $mobile = $ar['game_userid'];
                $pervalue = $ar['game_money'];
                $orderid  = $ar['oid'];
                $res = $huafei_S->telcz($mobile,$pervalue,$orderid);
                break;
            case '流量':    
                $ll_S = new \app\service\juhe_liuliang();
                $mobile = $ar['game_userid'];
                $pid = $ar['card_id'];
                $orderid  = $ar['oid'];
                $res = $ll_S -> telcz($mobile,$pid,$orderid);
                break;
            case '油卡':
                $youka_S = new \app\service\juhe_youka();
                $proid = $ar['card_id'];
                $cardnum = 1;
                $orderid  = $ar['oid'];
                $game_userid = $ar['game_userid'];
                $gas_tel = $ar['gas_tel'];
                $res = $youka_S->cardcz($proid,$cardnum,$orderid,$game_userid,$gas_tel);
                break;
        }

            if(isset($res['result']['sporder_id'])){
                $juhe_oid = $res['result']['sporder_id'];
                $this->juhe_M->up($id,['juhe_oid'=>$juhe_oid]); //聚合交易单号
            }

            $model->run();
            $redis->exec();

        $result['oid'] = $orderid;
        $result['pay_time']  = time();
        $result['money'] = $ar['money']; 
        $result['is_pay'] = 1;
        return $result;    
    }

  
}
