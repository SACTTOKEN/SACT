<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 */
namespace app\validate;

class LivingPayValidate extends BaseValidate
{
    protected $rule = [
        'money'    => 'require|isMoney|in:30,50',
        'title'    => 'require',
        'city'     => 'require',
        'types'    => 'require',
        'company'  => 'require',
        'number'   => 'require',
        'status'   => 'require',
    ];

    protected $message = [
        'money.require'     => '金额必须',
        'money.isMoney'     => '金额格式不正确',
        'title'             => '缴费户名必须',
        'city'              => '缴费城市必须',
        'types'             => '缴费类型必须',
        'company'           => '缴费单位必须',
        'number'            => '缴费户号必须',
        'status'            => '审核状态必须',
    ];

    protected $scene  = [       
        'scene_saveadd' =>  ['money','title','city','types','company','number'],
        'scene_types'   =>  ['types'],
        'scene_check'   =>  ['status'],
    ];
}
