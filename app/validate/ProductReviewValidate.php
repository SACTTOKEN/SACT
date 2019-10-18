<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 09:27:35
 * Desc: 商品评论验证
 */

namespace app\validate;

class ProductReviewValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'uid'    => 'require',
        'pid'  => 'require',
        'content'  => 'require',
        'star'  => 'require|in:1,2,3,4,5',
    ];

    
    protected $message = [
        'id'       => '请提交ID',
        'uid'    => '请提交用户ID',
        'pid'  => '请提交商品ID',
        'content'  => '请提交评价内容',
        'star'  => '请选择评价星级',
    ];

    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id_str'=>'require|checkcartid'],   
        'scene_add'   =>  ['uid','pid'],
        'scene_edit'  =>  ['id'],
        'mobile_saveadd'=>['content','star'],
    ];


}
