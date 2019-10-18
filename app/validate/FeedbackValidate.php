<?php
namespace app\validate;

class FeedbackValidate extends BaseValidate
{
    protected $rule = [
        'man' => 'require',
        'tel' => 'require',
        'content' => 'require',
        'types' => 'require',
    ];

    protected $message = [
        'man'     => '请填写联系人',
        'tel'     => '请填写联系电话', 
        'content' => '请填写您的问题',
        'types'   => '请选择反馈类型',
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['man','tel','content','types'],
    ];
}
