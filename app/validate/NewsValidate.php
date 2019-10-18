<?php
namespace app\validate;

class NewsValidate extends BaseValidate
{
    protected $rule = [  
        'id' 	   => 'require',
        'title'    => 'require',      
        'cate_id' => 'require',
        'id_str' => 'require|checkcartid',    
    ];

    protected $message = [
        'title'   => '标题必须',
        'cate_id' => '类别必须', 
    ];


	protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_list'  =>  ['cate_id'],
        'scene_add'   =>  ['cate_id','title'],
        'scene_checkID' =>  ['id_str'],
    ];

   

}
