<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-15 09:16:11
 * Desc: 配置验证
 */
namespace app\validate;

class SkuValidate extends BaseValidate
{
    protected $rule = [  
        'id' 	   => 'require',
        'title'    => 'require',      
        'parent_id' => 'require',    
    ];


     protected $message = [
        'title'    => '名称必须',  
        'parent_id'    => '父类别必须',      
    ];



	protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_list'  =>  ['parent_id'],
        'scene_add'   =>  ['parent_id','title'=>'require|unique:product_sku'],
    ];

}
