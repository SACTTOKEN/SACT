<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-10 14:58:21
 * Desc: 早起签到验证
 */
namespace app\validate;

class PluginFingerValidate extends BaseValidate
{
    protected $rule = [
        'stake'    	=> 'require|isMoney',
        'choose_1'  => 'require',
        'choose_2'  => 'require',

 	];


    protected $message = [
        'choose_1'    => '请出拳',
        'choose_2'    => '请出拳',
        'stake.require'       => '请下注',
        'stake.isMoney'       => '下注金额必须是正整数',
    ];

    protected $scene  = [
        'scene_publish'   =>  ['choose_1','stake'],
      'scene_challenge'   =>  ['choose_2'],
    ];




}
