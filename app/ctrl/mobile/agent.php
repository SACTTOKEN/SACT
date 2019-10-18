<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 17:02:46
 * Desc: 商户
 */

namespace app\ctrl\mobile;

use app\model\user as UserModel;

class agent extends PublicController
{
    public $user_M;
    public $user_attach_M;
    public $productM;
    public $order_M;
    public function __initialize()
    {
        if (!plugin_is_open('gbfx')) {
            error('未开启代理商版本', 10007);
        }
        $this->user_M = new UserModel();
        $this->user_attach_M = new \app\model\user_attach();
        $this->productM = new \app\model\product();
        $this->order_M = new \app\model\order();
    }


    //申请显示字段
    public function find_apply()
    {
        if ($GLOBALS['user']['is_agent'] == 1) {
            error(['info'=>'已经是代理商无需再审核','url'=>'/agent/index'],10008);
        }
        $agent_M = new \app\model\agent();
        $apply_res = $agent_M->have(['uid' => $GLOBALS['user']['id']]);
        $datas = (new \app\model\config())->list_cate('agent_apply');
        $data['config'] = array_column($datas, null, 'iden');
        $data['content'] = $apply_res;
        return $data;
    }


    //保存申请
    public function apply()
    {
        if ($GLOBALS['user']['is_agent'] == 1) {
            error(['info'=>'已经是代理商无需再审核','url'=>'/agent/index'],10008);
        }
        $agent_M = new \app\model\agent();
        $apply_res = $agent_M->have(['uid' => $GLOBALS['user']['id']]);
        if (!empty($apply_res)) {
            if ($apply_res['is_check'] == 1) {
                error('已审核通过', 404);
            } else {
                error('审核中，请等待通知', 404);
            }
        }
        $agent_V = new \app\validate\AgentValidate();
        if (c('agent_company_title')) {
            $agent_V->goCheck('agent_company_title');
            $data['title'] = post('title');
        }
   
        $agent_V->goCheck('agent_company_region');
        $data['province'] = post('province');
        $data['city'] = post('city');
        $data['area'] = post('area');
        $data['town'] = post('town');
        $data['types'] = post('types');
    
        if (c('agent_company_add')) {
            $agent_V->goCheck('agent_company_add');
            $data['add'] = post('add');
        }
        if (c('agent_company_name')) {
            $agent_V->goCheck('agent_company_name');
            $data['name'] = post('name');
        }
        if (c('agent_company_card')) {
            $agent_V->goCheck('agent_company_card');
            $data['card'] = post('card');
        }
        if (c('agent_company_cardpositive')) {
            $agent_V->goCheck('agent_company_cardpositive');
            $data['cardpositive'] = post('cardpositive');
        }
        if (c('agent_company_cardnegative')) {
            $agent_V->goCheck('agent_company_cardnegative');
            $data['cardnegative'] = post('cardnegative');
        }
        if (c('agent_company_license')) {
            $agent_V->goCheck('agent_company_license');
            $data['license'] = post('license');
        }
      
        $data['uid'] = $GLOBALS['user']['id'];
        $res = $agent_M->save($data);
        empty($res) && error('申请错误', 10006);
        return '申请成功';
    }


    //代理商中心
    public function member()
    {
        $this->is_agent();
        $user = $GLOBALS['user'];
        $money_M=new \app\model\money();
        $data['agent_order']=$money_M->new_count(['uid'=>$user['id'],'iden'=>'agentaward']);
        $data['agent_reward']=$money_M->find_sum('money',['uid'=>$user['id'],'iden'=>'agentaward']);
        $data['is_agent']=$user['is_agent'];
        $data['agent_province']=$user['agent_province'];
        $data['agent_city']=$user['agent_city'];
        $data['agent_area']=$user['agent_area'];
        $data['agent_town']=$user['agent_town'];
        return $data;
    }

    //流水
    public function running_water()
    {
        (new \app\validate\PageValidate())->goCheck();
        $user = $GLOBALS['user'];
        $page = post("page",1);
        $page_size = post("page_size",10);
        $money_M=new \app\model\money();
        $where['iden']='agentaward';
        $water=$money_M->lists_one($user['id'],$page,$page_size,$where);
		
        foreach($water as &$vo){
            if($vo['oid']=='无'){
                unset($vo['oid']);
            }
            if($vo['ly_id']==$vo['uid']){
                unset($vo['ly_id']);
            }
            if(isset($vo['ly_id'])){
                $users=user_info($vo['ly_id']);
                $vo['ly_nickname']=$users['nickname']?$users['nickname']:$users['username'];
            }
        }
        return $water;
    }

    
    public function is_agent()
    {
        if (!$GLOBALS['user']['is_agent']) {
            error('你不是代理商', 10007);
        }
    }
}
