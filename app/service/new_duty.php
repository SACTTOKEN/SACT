<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-10 10:27:57
 * Desc: 新手任务
 */
namespace app\service;
use app\model\new_duty as NewDutyModel;
use app\model\user as UserModel;

class new_duty{

    //调用示例：
    //$new_duty_S = new \app\service\new_duty();
    //$new_duty_S->paid_reward('116','wcgrzl');
    //备注：YES 完成个人资料 完成实名认证  收藏一件商品 完成一次购物 评价一次商品 申请一次提现佣金 分享一个好友(推荐人第一个)
    //NO  分享一篇文章 与好友第一次聊天 
    
    public function paid_reward($uid,$iden){
        $plugin_M = new \app\model\plugin();
        $res = $plugin_M->find_open('sqhb');
        if(!$res){
            return;
        }
        $money_S = new \app\service\money();
        $config_M = new \app\model\config();
        $new_duty_M = new \app\model\new_duty();

        $is_allow = $config_M->find('is_'.$iden); //is_news_mission类的配置是否开放该项目
        if(!$is_allow){
            return;
        }

        $config_ar = $config_M->have(['iden'=>$iden]);

        $balance_type = $config_ar['help'];
        //$balance_type_cn = find_reward_redis($balance_type);
        $duty = $config_ar['title'];

        $score_plus = $config_ar['value'];

        $where['uid'] = $uid;
        $where['iden'] = $iden;
        $is_have = $new_duty_M->is_have($where); //先判断new_duty里是否存在,存在即已完成该任务
        if(empty($is_have)){

            flash_god($uid);
            $model = new \core\lib\Model();
            $redis = new \core\lib\redis();  
            $model->action();
            $redis->multi();

                $data['uid'] = $uid;
                $data['iden'] = $iden;
                $data['duty'] = $duty;
                $data['score_plus'] = $score_plus;
                $data['balance_type'] = $balance_type;

                $new_ar = $new_duty_M ->save_by_oid($data);
                $oid = $new_ar['oid'];

                $remark  = $duty;
                if($balance_type == 'integral'){
                    $sy = 'sum_integral';
                }
                if($balance_type == 'amount'){
                    $sy = 'sum_amount';
                }

            $money_S -> plus($uid,$score_plus,$balance_type,'news_mission',$oid,$uid,$remark,$sy);

            $model->run();
            $redis->exec();       
        }
        return true;
    }


    
    public function paid_reward_noredis($uid,$iden){
        $plugin_M = new \app\model\plugin();
        $res = $plugin_M->find_open('sqhb');
        if(!$res){
            return;
        }
        $money_S = new \app\service\money();
        $config_M = new \app\model\config();
        $new_duty_M = new \app\model\new_duty();

        $is_allow = $config_M->find('is_'.$iden); //is_news_mission类的配置是否开放该项目
        if(!$is_allow){
            return;
        }

        $config_ar = $config_M->have(['iden'=>$iden]);

        $balance_type = $config_ar['help'];
        //$balance_type_cn = find_reward_redis($balance_type);
        $duty = $config_ar['title'];

        $score_plus = $config_ar['value'];

        $where['uid'] = $uid;
        $where['iden'] = $iden;
        $is_have = $new_duty_M->is_have($where); //先判断new_duty里是否存在,存在即已完成该任务
        if(empty($is_have)){


                $data['uid'] = $uid;
                $data['iden'] = $iden;
                $data['duty'] = $duty;
                $data['score_plus'] = $score_plus;
                $data['balance_type'] = $balance_type;

                $new_ar = $new_duty_M ->save_by_oid($data);
                $oid = $new_ar['oid'];

                $remark  = $duty;
                if($balance_type == 'integral'){
                    $sy = 'sum_integral';
                }
                if($balance_type == 'amount'){
                    $sy = 'sum_amount';
                }

            $money_S -> plus($uid,$score_plus,$balance_type,'news_mission',$oid,$uid,$remark,$sy);
  
        }
        return true;
    }

}