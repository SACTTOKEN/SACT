<?php
namespace app\validate;

class BannerValidate extends BaseValidate
{
    protected $rule = [
    	'iden'     => 'require|unique:banner',   	
        'title'    => 'require',      
        'cate'     => 'require',
    ];

    protected $message = [
        'iden'     =>  '标签丢失',
        'title'    => '标题丢失', 
        'cate'     => '类别丢失',
    ];


	protected $scene  = [
        'scene_find'  =>  ['iden'=>'require'],
        'scene_add'   =>  ['iden'],
        'scene_list'  =>  ['cate'],  
    ];

}
