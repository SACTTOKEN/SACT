<?php
namespace app\validate;

class DragFollowValidate extends BaseValidate
{
    protected $rule = [   
        'follow_id'          => 'require',
        'follow_time'  => 'require',  
        'tel'          => 'require', 
        'custom_type' => 'require', 
    ];

    protected $message = [
        'follow_id'    => '商户与客户关系ID丢失', 
        'follow_time'  => '请填写跟进时间',
        'custom_type' => '客户类型必选',
    ];

    protected $scene  = [
        'scene_follow'     => ['follow_id','follow_time'],
        'scene_type'     => ['follow_id','custom_type'],
    ];
}
