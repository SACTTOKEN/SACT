<?php
namespace app\validate;

class GdxqszValidate extends BaseValidate
{
    protected $rule = [
	    'title'    => 'require|chsDash', 
        
    ];

    protected $message = [
	    'title.require'    => '请提交等级标题', 
        'title.chsDash'    => '只能是汉字、字母、数字和下划线_及破折号-', 
       
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['title'],
        'scene_saveedit'  =>  ['title'],
    ];
}
