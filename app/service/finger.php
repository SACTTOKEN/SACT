<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-11 13:40:31
 * Desc: 猜拳服务
 */
namespace app\service;

class finger{
    public $finger_M;
    public function __construct()
    {
        $this->finger_M = new \app\model\plugin_finger_lord(); 
    }
    
    /*猜拳奖励发放,只有一方得奖*/
    public function finger_reward($finger_id){
        $money_S = new \app\service\money();

        $ar = $this->finger_M->find($finger_id);
        if($ar['is_end']==1){error('已结算',400);}

        $oid = $ar['oid'];
        $choose_1 = $ar['choose_1'];
        $choose_2 = $ar['choose_2'];
        $user_1 = $ar['user_1'];
        $user_2 = $ar['user_2'];
       
        $balance_type = $ar['balance_type'];

        //0：石头  1：剪刀  2：布
        // draw:平局  win:胜  lose:输
        $win = [];
        $win[0][0] = 'draw';//"平局!你出石头,对方也出石头";
        $win[0][1] = 'win';//"胜！你出石头,对方出剪刀";
        $win[0][2] = 'lose';//"输！你出石头,对方出布";
        $win[1][0] = 'lose';//"输！你出剪刀,对方出石头";
        $win[1][1] = 'draw';//"平局!你出剪刀,对方也出剪刀";
        $win[1][2] = 'win';//"胜！你出剪刀,对方出布";
        $win[2][0] = 'win';//"胜！你出布,对方出石头";
        $win[2][1] = 'lose';//"输！你出布,对方出石头";
        $win[2][2] = 'draw';//"平局!你出布,对方也出布";

        $result = $win[$choose_2][$choose_1];

        switch ($result) {
            case 'win':
                $remark = '胜出';
                $win_uid = $user_2;
                $charge = $ar['rate'] * $ar['stake']/1000; //服务费
                $money = $ar['stake']*2 - $charge;            

                $data['charge'] = $charge;
                $data['earn_2'] = $ar['stake'] - $charge;   
                $data['winner'] = 2;
                $data['is_end'] = 1;
                $data['war'] = 2;

                $this->finger_M->up($finger_id,$data);
                $money_S->plus($win_uid,$money,$balance_type,'cuaiquan',$oid,$win_uid,$remark);
                break;

            case 'lose':
                $remark = '胜出';
                $win_uid = $user_1;
                $charge = $ar['rate'] * $ar['stake']/1000; //服务费
                $money = $ar['stake']*2 - $charge;      

                $data['charge'] = $charge;
                $data['earn_1'] = $ar['stake'] - $charge;   
                $data['winner'] = 1;
                $data['is_end'] = 1;
                $data['war'] = 2;
                $this->finger_M->up($finger_id,$data);
                $money_S->plus($win_uid,$money,$balance_type,'cuaiquan',$oid,$win_uid,$remark);
                break;    

            case 'draw':
                $remark = '平局'; 
                $money = $ar['stake'];
                $data['charge'] = 0; //无服务费
                $data['winner'] = 3;
                $data['is_end'] = 1;
                $data['war'] = 2;
                $this->finger_M->up($finger_id,$data);

                $money_S->plus($user_1,$money,$balance_type,'cuaiquan',$oid,$user_1,$remark);
                $money_S->plus($user_2,$money,$balance_type,'cuaiquan',$oid,$user_2,$remark);
                break;
        }

        $back['choose_1'] = $choose_1;
        $back['choose_2'] = $choose_2;
        $back['desc'] =  $result;//draw:平局  win:胜  lose:输
        return $back;
    }

    
}