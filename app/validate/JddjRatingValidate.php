<?php
namespace app\validate;

class JddjRatingValidate extends BaseValidate
{
    protected $rule = [
        'title'    => 'require|chsDash', 
        'zt_num'    => 'number', 
        'sxyj'    => 'number', 
        'ztjd_num'    => 'number', 
        'ztjd_id'    => 'number', 
        'jlqfb'    => 'number', 
    ];

    protected $message = [
        'title.require'    => '请提交等级标题', 
        'title.chsDash'    => '只能是汉字、字母、数字和下划线_及破折号-', 
        'zt_num'    => '直推人数只能是正整数', 
        'sxyj'    => '伞下业绩只能是正整数', 
        'ztjd_num'    => '直推节点人数只能是金额类型', 
        'ztjd_id'    => '请正常选择直推节点等级', 
        'jlqfb'    => '伞下所有收益只能是正整数', 
    ];


	protected $scene  = [
        'add'  =>  ['title','zt_num','sxyj','sxyj','ztjd_num','ztjd_id','jlqfb'],
        'edit'  =>  ['title','zt_num','sxyj','sxyj','ztjd_num','ztjd_id','jlqfb'],
    ];

}
