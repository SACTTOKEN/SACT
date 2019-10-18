<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-15 09:16:11
 * Desc: 配置验证
 */
namespace app\validate;

class ProductCateValidate extends BaseValidate
{
    protected $rule = [  
        'id' 	   => 'require',
        'title'    => 'require',      
        'parent_id' => 'require',    
    ];

    protected $message = [
        'title'    => '分类标题必须',
        'parent_id' => '请选择父类别',

    ];

    
	protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_list'  =>  ['parent_id'],
        'scene_add'   =>  ['parent_id','title'=>'require'],
    ];

}
