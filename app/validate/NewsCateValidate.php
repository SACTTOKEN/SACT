<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-15 09:16:11
 * Desc: 配置验证
 */
namespace app\validate;

class NewsCateValidate extends BaseValidate
{
    protected $rule = [  
        'id' 	   => 'require',
        'title'    => 'require|unique:news_cate',      
        'parent_id' => 'require',    
    ];

    protected $message = [
        'uid'      => '用户ID必须',
        'title.require'    => '标题必须',
        'title.unique'    => '标题已存在',
        'parent_id' => '类别必须',
     
    ];



	protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_list'  =>  ['parent_id'],
        'scene_add'   =>  ['parent_id','title'],
    ];

}
