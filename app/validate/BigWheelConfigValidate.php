<?php
namespace app\validate;

class BigWheelConfigValidate extends BaseValidate
{
    protected $rule = [
        'title'     => 'require', 
        'readme'    => 'require',
    ];

    protected $message = [
        'title'      => '请填写活动名称', 
        'readme'    => '请填写活动说明',
        
    ];

    protected $scene  = [
       'scene_add'   =>  ['title','readme'],
    ];

}
