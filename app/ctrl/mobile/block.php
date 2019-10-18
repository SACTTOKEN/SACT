<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-16 13:51:24
 * Desc: 收货地址
 */
namespace app\ctrl\mobile;

class block extends PublicController
{
	public $block_wallet_M;

	public function __initialize(){
		$this->block_wallet_M = new \app\model\block_wallet();
    }
    

    public function recharge()
    {
        if(c('recharge_coin')!=1){
            error('敬请期待',10007);
        }
        $user = $GLOBALS['user'];
        $res=$this->block_wallet_M->have(['uid'=>$user['id']]);
        if($res){
            $data['effective_time']=time()+c('qbfp_sdsj');
            $data['freed_time']=$data['effective_time']+c('qbfp_ycsj');
            $data['carried_time']=time()+5;
            $res_2=$this->block_wallet_M->up($res['id'],$data);
            empty($res_2) && error('生成失败',10006);
            $id=$res['id'];
        }else{
            flash_god($user['id']);
            $qbfp_sdsj=c('qbfp_sdsj');
            $qbfp_ycsj=c('qbfp_ycsj');
            $redis = new \core\lib\redis();
            $Model = new \core\lib\Model();
            $Model->action();
            $redis->multi();

            $res_1=$this->block_wallet_M->have(['status'=>0,'assignable_time[<]'=>time()]);
            if($res_1){
                //还有可用钱包直接拿去用
                $data['status']=1;
                $data['distribution_time']=time();
                $data['carried_time']=time()+5;
                $data['uid']=$user['id'];
                $data['effective_time']=time()+$qbfp_sdsj;
                $data['freed_time']=$data['effective_time']+$qbfp_ycsj;
                $block=new \extend\block\ETHBances();
                $key=$block->index($res_1['publickey']);
                if($key>0){
                    $data['distribution_money']=$key;
                    $data['account_money']=$key;
                }
                $res_2=$this->block_wallet_M->up($res_1['id'],$data);
                empty($res_2) && error('生成失败',10006);
                $id=$res_1['id'];
            }else{
                //没有可用钱包去创建一个把
                $block=new \extend\block\CreateEth();
                $key=$block->index();
                if($key){
                    $data['publickey']=$key[0];
                    $data['privatekey']=$key[1];
                    $data['status']=1;
                    $data['distribution_time']=time();
                    $data['carried_time']=time()+5;
                    $data['uid']=$user['id'];
                    $data['cate']='ETH';
                    $data['effective_time']=time()+$qbfp_sdsj;
                    $data['freed_time']=$data['effective_time']+$qbfp_ycsj;
                    $res_2=$this->block_wallet_M->save($data);
                    empty($res_2) && error('生成失败',10006);
                    $id=$res_2;
                }
            }

            $Model->run();
            $redis->exec();
        }
        $res_3=$this->block_wallet_M->have(['id'=>$id,'uid'=>$user['id']],'publickey');
        empty($res_3) && error('生成失败',10006);
        return $res_3;
    }

    public function is_success()
    {
        (new \app\validate\BlockValidate())->goCheck('is_success');
        $publickey=post('publickey');
        $user = $GLOBALS['user'];
        $where['uid']=$user['id'];
        $where['publickey']=$publickey;
        $where['AND']=['account_money[>]distribution_money'];
        $res=$this->block_wallet_M->is_have($where);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    //充值流水
    public function recharge_lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
        $page_size = post("page_size",10);		
        $coin_recharge_M=new \app\model\block_recharge();
        $where['uid']=$user['id'];
        $ar=$coin_recharge_M->lists($page,$page_size,$where);
        foreach($ar as &$vo){
            unset($vo['publickey']);
            unset($vo['distribution_money']);
            unset($vo['account_money']);
        }
        return $ar;
    }



}