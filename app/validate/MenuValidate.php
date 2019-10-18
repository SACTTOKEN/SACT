<?php
namespace app\validate;

class MenuValidate extends BaseValidate
{
    protected $rule = [
        'title' => 'require',
        'parent_id' => 'require|number',
    ];



    protected $message = [
        'title'    => '题标不能为空',
        'parent_id'    => '请选择类别',
    ];

}
