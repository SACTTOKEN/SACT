<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 */
namespace app\validate;

class RobTimeValidate extends BaseValidate
{
    protected $rule = [
        'begin_time' => 'require',
        'end_time'   => 'require',
        'price' => 'require|isMoney',
        'discount' => 'require|number',
        'time_id'  => 'require',
        'score' => 'require',
    ];

    protected $message = [
        'begin_time'    => '开始时间必须',  
        'end_time'      => '结束时间必须',
        'money.require'    => '金额不能为空',   
        'money.isMoney'    => '金额必须是正整数',    
        'discount'     => '折扣必须是数字,不打折填10',
        'time_id' => '请选择限时区间',
        'score'   => '兑换的积分必须',
    ];

    protected $scene  = [
        'scene_add'   =>  ['begin_time','end_time'],
        'scene_copy'  =>  ['discount','time_id'],
        'scene_score' =>  ['score'],
    ];
}
