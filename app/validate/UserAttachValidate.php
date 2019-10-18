<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 09:34:59
 * Desc: 用户验证
 */


namespace app\validate;

class UserAttachValidate extends BaseValidate
{
    protected $rule = [
        'uid'       => 'require',
        'alipay'	=>  'require|max:25',
        'alipay_name' =>'require|chs',
        'wechat'	=>  'require|max:25',
        'bank_card' =>  'require|min:12|max:20',
        'bank_name' =>  'require|chs',
        'bank'      =>  'chs',
        'bank_network' =>  'chs',
        'shop_latitude' =>  'number|egt:0',
        'shop_longitude' =>  'number|egt:0',
        'shop_fee' =>  'number|egt:0',
        'shop_referrer' =>  'number|egt:0',
    ];

    protected $message = [
		'alipay'			  => '亲，请写支付宝账号',
        'alipay_name.require' => '亲，请填写支付宝姓名',
        'alipay_name.chs'     => '亲，支付宝姓名只能是中文哟',	
		'wechat'			  => '亲，请写微信账号',
		'bank_card'           => '亲，请填写银行卡号',
		'bank_card.min'       => '亲，银行卡号最少12位',
		'bank_card.max'       => '亲，银行卡号最多20位',
		'bank_name.require'   => '亲，请填写银行户名',
        'bank_name.chs'       => '亲，银行户名只能是中文哟',
        'bank'                => '亲，开户行只能是中文哟',
        'bank_network'        => '亲，开户网点只能是中文哟',
        'shop_latitude'        => '请填写正确的经纬度',
        'shop_longitude'        => '请填写正确的经纬度',
        'shop_fee'        => '请填写正确的供应商服务费',
        'shop_referrer'        => '请填写正确的供应商推荐奖',


    ];

    protected $scene  = [
        'scene_find'  =>  ['uid'],
        'scene_del'   =>  ['uid'],
        'scene_add'   =>  ['uid'=>'require|unique:user_attach'],
        'scene_edit'  =>  ['uid'],
        'scene_collections_edit' => ['alipay','alipay_name','wechat','bank_card','bank_name','bank','bank_network'],
        'supplier'=>['shop_latitude','shop_longitude','shop_fee','shop_referrer']
    ];

}
