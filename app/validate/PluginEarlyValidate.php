<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-10 14:58:21
 * Desc: 早起签到验证
 */
namespace app\validate;

class PluginEarlyValidate extends BaseValidate
{
    protected $rule = [
        'stage'    	  => 'require',
        'stake'    	  => 'require|isMoney',
        'stage_title' => 'require|unique:plugin_early_lord',
        'end_time'    => 'require',
 	];

	protected $message = [
		'stage_title.require'       => '请填写期数名称',
		'stage_title.unique'       => '期数名称已使用',
		'end_time'          => '请填写签到日期',	
		'stake.require'          => '请填写签到金额',	
		'stake.isMoney'          => '签到金额必须是正整数',	
    ];

    protected $scene  = [
        'scene_add'   =>  ['stage_title','end_time'=>'require'],
        'scene_buy'   =>  ['stake'],
        'scene_sign'  =>  ['stage'],
    ];



}
