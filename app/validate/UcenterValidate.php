<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 09:34:59
 * Desc: 用户中心验证
 */


namespace app\validate;

class UcenterValidate extends BaseValidate
{
    protected $rule = [
        'nickname'  => 'require|max:20|chsAlpha',
        'mobile'    => 'require|isMobile',
        'avatar'    =>  'require',
        'name'	    =>  'require',
        'alipay'	=>  'require|max:25',
        'alipay_name' =>'require',
        'wechat'	=>  'require|max:25',
        'realName'	=>  'require',
        'cardNo'	=>  'require',
        'card_face'	=>  'require',
        'card_bg'	=>  'require',
        'password'	=>  'require',
        'bank_card' =>  'require|min:12|max:20',
        'bank_name' =>  'require',
        'tel'       =>  'require|isMobile',
        'address'   =>  'require|max:100',
        'new_password'=>'require',
        'alipay_pic'=>'require',
        'wechat_pic'=>'require',
        'bank'=>'require',
        'bank_network'=>'require',
        'bank_province'=>'require',
        'bank_city'=>'require',
    ];
	
	protected $message = [
		'nickname.require'    => '亲，请填写昵称',
        'nickname.max' 		  => '亲，昵称不能超过二十位',
        'nickname.chsAlpha'   => '亲，昵称只能中文和字母',
        'moblie.isMobile' 	  => '亲，手机格式不正确',
        'avatar'			  => '亲，请上传社交头像',
        'name'				  => '亲，请写真实姓名',	
		'alipay.require'	  => '亲，请写支付宝账号',
		'alipay_name' 		  => '亲，请填写支付宝姓名',	
		'wechat.require'	  => '亲，请写微信账号',
		'realName'			  => '亲，请写真实姓名',	
		'cardNo.require'	  => '亲，请写身份证号',
		'alipay.max'		  => '亲，支付宝账号不能超过25位',	
		'wechat.max'		  => '亲，微信账号不能超过25位',
		'card_face'		  	  => '亲，请上传身份证正面',
		'card_bg'		      => '亲，请上传身份证背面',
		'password'            => '亲，请填写新密码',
		'bank_card'           => '亲，请填写银行卡号',
		'bank_card.min'       => '亲，银行卡号最少12位',
		'bank_card.max'       => '亲，银行卡号最多20位',
		'bank_name'           => '亲，请填写银行户名',
		'tel' 	  	          => '亲，请填写手机号',
		'tel.isMobile'        => '亲，手机格式不正确',
        'address'             => '亲，请填写详细地址',
        'new_password'        => '亲，请填写修改密码',
        'alipay_pic'          => '亲，请上传支付宝收款码',
        'wechat_pic'          => '亲，请上传微信收款码',
        'bank'                => '亲，请选择开户行',
        'bank_network'        => '亲，请填写开户网点',
        'bank_province'       => '亲，请选择开户省',
        'bank_city'           => '亲，请选择开户市',
    ];

    protected $scene  = [
        'scene_edit_userinfo' =>  ['nickname'],
        'scene_complete_info' =>  ['nickname','avatar'],  
        'scene_my_wallet'     =>  ['name','alipay','wechat'],
        'scene_sfrz'		  =>  ['realName','cardNo'],
        'scene_change_password' => ['tel','new_password'],
        'scene_bind_account' => ['bank_card','bank_name','bank','bank_network','bank_province','bank_city'],
        'scene_alipay' => ['alipay','alipay_name','alipay_pic'],
        'scene_wechat' => ['wechat','wechat_pic'],
        'scene_add_address' => ['name','tel','address'],
    ];

    

}
