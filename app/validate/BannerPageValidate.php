<?php
namespace app\validate;

class BannerPageValidate extends BaseValidate
{
    protected $rule = [
    	'iden'     => 'require|in:home,vip',   	
        'head'    => 'require',      
        'logo'     => 'require',
        'background'     => 'require',
        'del_module'    =>'checkcartid',
        'del_banner'    =>'checkcartid',
    ];

    protected $message = [
        'iden'     =>  '请提交标识', 	
        'head'    => '请提交网站标题',      
        'logo'     => '请提交网站logo',
        'background'     => '请提交页面背景',
        'del_module'    =>'请上传模块ID',
        'del_banner'    =>'请上传广告ID',
    ];


	protected $scene  = [
        'iden'  =>  ['iden'],
        'scene_add'   =>  ['iden','head','logo','del_module','del_banner'],
        'del_banner'   =>  ['del_banner'],
    ];
}
