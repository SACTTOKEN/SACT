<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 图片附件验证
 */

namespace app\validate;

class ImageValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'aid'    => 'require',
        'cate'  => 'require',
        'piclink'  => 'require',
    ];


    protected $message = [
        'aid'     => '商品ID必须',
        'cate'    => '类别必须', 
        'piclink' => '图片必须', 
    ];



    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['aid','cate','piclink'],
        'scene_edit'  =>  ['id'],
        'scene_lists'  =>  ['aid','cate'],
    ];

}
