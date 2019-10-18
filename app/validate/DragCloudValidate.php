<?php
namespace app\validate;

class DragCloudValidate extends BaseValidate
{
    protected $rule = [
        'pid'          => 'require',
        'sid'          => 'require',  
        'tid'          => 'require', 
        'tel'          => 'require|isMobile',
        'link'         => 'require',
        'tel_str'      => 'require',
    ];

    protected $message = [
        'pid.require'    => '商品ID丢失',
        'sid.require'    => '发布者ID丢失', 
        'tid.require'    => '推荐者ID丢失',  
        'tel'            => '手机号格式不正确',  
        'link'           => '请提交链接地址',
        'tel_str'        => '请提交手机号',
    ];

    protected $scene  = [
        'give_honor'     => ['tel'],
        'wx_link'        => ['link'],
        'sid_lists'      => ['sid'],
        'add_mytel'      => ['tel_str'],
    ];
}
