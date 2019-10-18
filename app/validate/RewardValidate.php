<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-01-31 11:36:51
 * Desc: 奖励名称验证
 */
namespace app\validate;

class RewardValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require|number',
        'iden'     => 'require|alphaDash',
        'title'    => 'require|chsAlphaNum',
        'show'     => 'require|number',
    ];

    protected $message = [
        'iden.require'    => '标识必须',  
        'iden.alphaDash'    => '标识只能是字母、数字和下划线_及破折号-',  
        'iden.unique'    => '标识已存在',  
        'title.require'    => '标题必须',  
        'title.chsAlphaNum'    => '标题必须汉字字母数字',      
    ];

    protected $scene  = [
        'scene_add'   =>  ['iden'=>'require|alphaDash|unique:reward','title'],
        'scene_find'  =>  ['id|number'],
        'scene_change'=>  ['id','show'], 
    ];
}
