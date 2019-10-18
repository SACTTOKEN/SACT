<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 09:34:59
 * Desc: 用户验证
 */


namespace app\validate;

class UserValidate extends BaseValidate
{
    protected $rule = [
        'id'         => 'require',
        'username'   => 'require',
        'imtoken'   => 'require',
        'code'       => 'require',
        'password'   => 'require|min:6',
        'pay_password'   => 'min:6',
        'value'      => 'require',
        'show'       => 'require|number',
        'quhao'       => 'require|isPositiveInteger',
        'id_str'     => 'require|checkcartid',
        'content'    => 'require',
        'rating'=>'require|isPositiveInteger',
        'coin_rating'=>'require|isPositiveInteger',
        'tel'=>'alphaDash|unique:user',
        'is_im_attention'=>'in:0,1',
        'wx_id' =>'require',
        'is_agent' =>'require|in:0,1,2,3',
        'agent_province' =>'require',
        'agent_city' =>'require',
        'agent_area' =>'require',
        'agent_town' =>'require',
    ];


    protected $message = [
        'imtoken'    => '请提交账户', 
        'username.require'    => '用户名必须', 
        'username.unique'    => '用户名已存在', 
        'username.alphaDash'    => '用户名只能是数字和字母', 
        'quhao.require'    => '区号必须', 
        'quhao.isPositiveInteger'    => '区号必须为整数', 
        'code'    => '验证码必须', 
        'password.require'    => '密码必须', 
        'password.min'    => '密码最少6位', 
        'pay_password.min'    => '支付密码最少6位', 
        'content'    => '内容必须', 
        'rating' => '等级必须为整数',
        'coin_rating' => '矿机等级必须为整数',
        'tel.alphaDash' => '手机号码格式错误',
        'tel.unique' => '手机号码已存在',
        'is_im_attention'=>'是否关注',
        'wx_id' => '微信ID丢失',
        'is_agent' =>'选择代理商',
        'agent_province' =>'请选择省',
        'agent_city' =>'请选择市',
        'agent_area' =>'请选择区',
        'agent_town' =>'请选择镇',
    ];

    protected $scene  = [
        'scene_find'   =>  ['id'],
        'scene_del'    =>  ['id_str'],
        'scene_add'    =>  ['username'=>'require|unique:user|alphaDash','password'],
        'scene_edit'   =>  ['id','value'],
        'scene_login'  => ['username','password'],
        'imtoken_login'  => ['imtoken'],
        'scene_bindmobile' => ['id','username'=>'require|unique:user|alphaDash','code'],
        'scene_edit_userinfo' =>['id','username'=>'require|unique:user|alphaDash'],
        'scene_check' => ['id','show'],
        'scene_letter' => ['content'],
        'rating_saveedit'=>['rating','coin_rating'],
        'admin_scene_add'    =>  ['password','quhao','pay_password','tel'],
        'is_im_attention'=>['is_im_attention'],
        'scene_wx_reg' =>['wx_id','code'],
        'agent_edit' =>['is_agent','agent_province','agent_city','agent_area','agent_town'],
    ];

  

}
