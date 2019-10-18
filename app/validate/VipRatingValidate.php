<?php
namespace app\validate;

class VipRatingValidate extends BaseValidate
{
    protected $rule = [
        'title'    => 'require|chsDash', 
        'ljrd'    => 'number', 
    ];

    protected $message = [
        'title.require'    => '请提交等级标题', 
        'title.chsDash'    => '只能是汉字、字母、数字和下划线_及破折号-', 
        'ljrd'    => '请填写累计入单', 
    ];


	protected $scene  = [
        'add'  =>  ['title','ljrd'],
        'edit'  =>  ['title','ljrd'],
    ];

}
