<?php
namespace app\validate;

class ComplaintValidate extends BaseValidate
{
    protected $rule = [
        'sid' => 'require|isPositiveInteger',
        'title' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'sid'    => '请提交商户ID',
        'title'    => '请选择投诉类型', 
        'content'    => '请填写投诉内容', 
    ];

    protected $scene  = [
        'add'   => ['sid','title','content'],
    ];
}
