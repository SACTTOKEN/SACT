<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-27 11:44:36
 * Desc: 公排
 */

namespace app\service;

class platoon
{

    public $c;
    public $money_S;
    public $user_M;
    public $platoon_M;
    public $platoon_pw_M;
    public function  __construct()
    {
        $this->c = (new \app\model\config())->list_cate_iden('platoon');
        $this->platoon_M = new \app\model\platoon();
        $this->platoon_pw_M = new \app\model\platoon_pw();
        $this->money_S = new \app\service\money();
        $this->user_M = new \app\model\user();
    }

    public function index($user)
    {
        $platoon_money = $this->c['platoon_money'];
        $platoon_types = $this->c['platoon_types'];
        if ($user['platoon'] >= $platoon_money) {
            if($platoon_types=='全球公排'){
                $res=$this->globals($user['id']);    //全球公排
            }else{
                $res=$this->member($user['id']);    //会员公排
            }
            $this->money_S->minus($user['id'], $platoon_money, 'platoon', 'platoon_weight',$res['oid'], $user['id'], '自动入公排', '');
            $this->point($res); //见点奖
            $user['platoon'] = $user['platoon'] - $platoon_money;
            if ($user['platoon'] >= $platoon_money) {
                $this->index($user);
            }
        }
        return true;
    }

    //全球公排
    public function globals($uid)
    {
        $platoon_points=$this->c['platoon_points'];
        if($platoon_points<2){
            $platoon_points=2;
        }
        $where['number[<]']=$platoon_points;
        $where['ORDER']=['level'=>'ASC','number'=>'ASC','point'=>'ASC'];
        $platoon_ar=$this->platoon_M->have($where);

        if(empty($platoon_ar)){
            $where2['ORDER']=['level'=>'DESC','point'=>'ASC'];
            $platoon_ar=$this->platoon_M->have($where2);
        }
        if(empty($platoon_ar)){
            $tid=0;
            $t_uid=0;
            $level=1;
            $point=1;
        }else{
            $tid=$platoon_ar['id'];
            $t_uid=$platoon_ar['uid'];
            $level=$platoon_ar['level']+1;
            $point=1+($this->platoon_M->have(['ORDER'=>['point'=>'DESC']],'point'));
        }
        $data['uid']=$uid;
        $data['tid']=$tid;
        $data['t_uid']=$t_uid;
        $data['point']=$point;
        $data['level']=$level;
        $data['status']=1;
        $res=$this->platoon_M->save_by_oid($data);
        empty($res) && error('入点位错误',404);
        if($tid){
            $this->platoon_M->up($tid,['number[+]'=>1]);
        }
        return $res;
    }

    //会员公排
    public function member($uid)
    {
        $pid=$this->platoon_M->have(['uid'=>$uid],'id');
        if(empty($pid)){
            $tid=$this->user_M->find($uid,'tid');
            if($tid){
                $pid=$this->platoon_M->have(['uid'=>$tid],'id');
            }
        }

        $platoon_points=$this->c['platoon_points'];
        if($platoon_points<2){
            $platoon_points=2;
        }
        if($pid){
            $where['id']=$this->platoon_pw_M->lists_all(['tid'=>$pid],'uid');
        }
        $where['number[<]']=$platoon_points;
        $where['ORDER']=['level'=>'ASC','number'=>'ASC','point'=>'ASC'];
        $platoon_ar=$this->platoon_M->have($where);

        if(empty($platoon_ar)){
            if($pid){
                $where2['id']=$this->platoon_pw_M->lists_all(['tid'=>$pid],'uid');
            }
            $where2['ORDER']=['level'=>'DESC','point'=>'ASC'];
            $platoon_ar=$this->platoon_M->have($where2);
        }
        if(empty($platoon_ar)){
            $tid=0;
            $t_uid=0;
            $level=1;
            $point=1;
        }else{
            $tid=$platoon_ar['id'];
            $t_uid=$platoon_ar['uid'];
            $level=$platoon_ar['level']+1;
            //按入
            //$point=1+($this->platoon_M->have(['ORDER'=>['point'=>'DESC']],'point'));
            //从最左边开始跳排
            $point=$platoon_ar['point']+pow($platoon_points,$platoon_ar['level']-1)*($platoon_ar['number']+1);
        }
        $data['uid']=$uid;
        $data['tid']=$tid;
        $data['t_uid']=$t_uid;
        $data['point']=$point;
        $data['level']=$level;
        $data['status']=1;
        $res=$this->platoon_M->save_by_oid($data);
        empty($res) && error('入点位错误',404);
        if($tid){
            $this->platoon_M->up($tid,['number[+]'=>1]);
        }
        $this->pw($res['id'],$res['id'],0);
        return $res;
    }

    public function pw($uid,$tid,$level)
    {
        $data['uid']=$uid;
        $data['tid']=$tid;
        $data['level']=$level;
        $this->platoon_pw_M->save($data);
        $tid=$this->platoon_M->find($tid,'tid');
        if($tid){
        $this->pw($uid,$tid,$level+1);
        }
    }

    //见点奖
    public function point($res)
    {
        $platoon_team_M=new \app\model\platoon_team();
        $platoon_account=$this->c['platoon_account'];
        $coin_team_ar=$platoon_team_M->lists_all();
        $tid=$res['id'];
        $remark=$res['level'].'层'.$res['oid'].'点位奖励';
        foreach($coin_team_ar as $vo){
            $t_ar=$this->platoon_M->find($tid,['tid','t_uid']);
            if(empty($t_ar) || $t_ar['tid']==0){
                return;
            }
            $sy='';
            if($platoon_account=='amount'){
                $sy='sum_amount';
            }else{
                $sy='sum_integral';
            }

            if($vo['reward']>0){
                $this->platoon_M->up($t_ar['tid'],['income[+]'=>$vo['reward']]);
                $this->money_S->plus($t_ar['t_uid'],$vo['reward'],$platoon_account,'see_rewards',$res['oid'],$res['uid'],$remark,$sy);
                if($vo['fee']>0){
                    $reward_fee=$vo['reward']*$vo['fee']/100;
                    if($reward_fee>0){
                        $this->money_S->minus($t_ar['t_uid'],$reward_fee,$platoon_account,'see_rewards',$res['oid'],$res['uid'],$remark.'手续费');
                    }
                }
            }
            $tid=$t_ar['tid'];
        }
    }
}
