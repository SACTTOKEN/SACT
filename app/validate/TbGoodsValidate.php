<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 14:19:55
 * Desc: 淘商品验证
 */

namespace app\validate;

class TbGoodsValidate extends BaseValidate
{
    protected $rule = [
        'f_id' => 'require',
        'keyward' => 'require',
        'url' => 'require',
        'pic' => 'require',
        'title' => 'require',
    ];

    protected $message = [
        'f_id'    => '类别ID必须',  
        'keyward'  => '亲，请输入关键词',
        'url'  => '请上传地址',
        'pic'  => '请上传图片',
        'title'  => '请上传标题',
    ];
    
    protected $scene  = [
        'scene_find'  =>  ['id','status'],
        'scene_vueSearch' =>['keyward'],
        'tbk_kl'=>['url','pic','title'],
    ];


}
