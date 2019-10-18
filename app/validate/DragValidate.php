<?php
namespace app\validate;

class DragValidate extends BaseValidate
{
    protected $rule = [
        'pid'          => 'require',
        'sid'          => 'require',  
        'tid'          => 'require',      
    ];

    protected $message = [
        'pid.require'    => '商品ID丢失',
        'sid.require'    => '发布者ID丢失', 
        'tid.require'    => '推荐者ID丢失',  
    ];

    protected $scene  = [
        'scene_over'     => ['pid'],
        'scene_custom'   => ['sid'],
    ];
}
