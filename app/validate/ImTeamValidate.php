<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-27 15:51:53
 * Desc: IM战队验证
 */

namespace app\validate;

class ImTeamValidate extends BaseValidate
{
    protected $rule = [
        'title'=>'require',
        'avatar'=>'require',
        'slogan'=>'require',
        'cate'=>'require',
        'zt'=>'number',
        'dd'=>'number',
        'is_open'=>'require|in:0,1',
        'is_chat'=>'in:0,1',
        'other_id'=>'isPositiveInteger',
        'im'=>'require'
    ];

    protected $message = [
        'title'=>'请填写战队名称',
        'avatar'=>'请上传战队头像',
        'slogan'=>'请填写战队口号',
        'cate'=>'请选择战队类别',
        'is_open'=>'请选择是否开放加入',
        'is_chat'=>'请选择是否禁言',
        'zt'=>'请选择直推人数条件',
        'dd'=>'请选择团队人数条件',
        'other_id'=>'请输入用户ID',
        'im'=>'请提交im账号',
    ];

    protected $scene  = [
        'add_create_team'=>['title','avatar','slogan','cate','zt','dd'],
        'edit_create_team'=>['title','avatar','slogan','zt','dd','is_open','is_chat'],
        'other_id'=>['other_id'],
        'im'=>['im'],
    ];

}
