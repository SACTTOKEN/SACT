<?php
namespace app\validate;

class ConfigValidate extends BaseValidate
{
    protected $rule = [
    	'iden'     => 'require|unique:coin_currency',   	
        'title'    => 'require',      
        'type'     => 'require',
        'cate'     => 'require',
        'value'    => 'require',
        'ar'       => 'require', 
    ];

    protected $message = [
        'iden.require'    => 'iden配置标识必须',
        'iden.unique'    => 'iden配置标识必须唯一',

        'cate'    => '类别必须', 
        'value'   => '配置值必须',
        'ar'      => '内容必须',
    ];

    
	protected $scene  = [
        'scene_find'     =>  ['iden'=>'require'],
        'scene_list'     =>  ['cate'],
        'scene_add'      =>  ['iden','value'],
        'scene_savelist' =>  ['ar'], 
        'scene_config_open' => ['iden'=>'require'], 
    ];

}
