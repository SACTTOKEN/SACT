<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 13:48:22
 * Desc: 用户留言控制器
 */

namespace app\ctrl\admin;


class user_seal extends BaseController{
    
    public $user_M;
    public $user_gx_M;
    public $user_attach_M;
	public function __initialize(){
        $this->user_M=new \app\model\user();
        $this->user_gx_M=new \app\model\user_gx();
        $this->user_attach_M=new \app\model\user_attach();
	}


    public function close(){
        $username = post('username');
        $id=$this->user_M->have(['username'=>$username],'id');
        empty($id) && error('用户不存在',404);
        $user_ar=$this->user_gx_M->lists_all(['tid'=>$id],'uid');
        $user_ar[]=$id;
        foreach($user_ar as $vo){
            $this->user_M->up($vo,['show'=>0]);
            $this->user_attach_M->up($vo,['admin_remark'=>'批量封号']);
        }
    }	

    public function open()
    {
        $username = post('username');
        $id=$this->user_M->have(['username'=>$username],'id');
        empty($id) && error('用户不存在',404);
        $user_ar=$this->user_gx_M->lists_all(['tid'=>$id],'uid');
        $user_ar[]=$id;
        foreach($user_ar as $vo){
            $this->user_M->up($vo,['show'=>1]);
            $this->user_attach_M->up($vo,['admin_remark'=>'批量解封']);
        }
        
    }
}