<?php
namespace app\validate;

class MxqszValidate extends BaseValidate
{
    protected $rule = [
	    'title'    => 'require|chsDash', 
        'ljrd' => 'number',
        'mtsc' => 'number',
		'gmid' => 'number',
    ];

    protected $message = [
	    'title.require'    => '请提交等级标题', 
        'title.chsDash'    => '只能是汉字、字母、数字和下划线_及破折号-', 
        'ljrd'   => '购买金额只能是正整数',
        'mtsc'    => '每天释放只能是正整数',
        'gmid'    => '请选择购买VIP等级',
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['title','zt_num','ljrd','mtsc','gmid'],
        'scene_saveedit'  =>  ['title','zt_num','ljrd','mtsc','gmid'],
    ];
}
