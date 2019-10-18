<?php
namespace app\validate;

class MailAreaValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'cid'    => 'require',
    ];


    protected $message = [
        'aid'     => '商品ID必须',
        'cate'    => '类别必须', 
        'piclink' => '图片必须', 
    ];

    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['cid'],
        'scene_edit'  =>  ['id'],
        'scene_lists'   =>  ['cid'],
    ];

}
