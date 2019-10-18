<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 自提验证
 */

namespace app\validate;

class ExtractValidate extends BaseValidate
{
    protected $rule = [
        'name'      => 'require|max:6',
        'tel'       => 'require|isMobile',
        'title'     => 'require',
        'province'  => 'require',
        'city'      => 'require',
        'area'      => 'require',
        'add'       => 'require',
    ];

    protected $message = [
        'title.require'    => '亲，请填写店名',
        'name.max'         => '亲，姓名不能超过六位',
        'tel.require'      => '亲，请填写手机号',
        'tel.isMobile'     => '亲，手机格式不正确',
        'province'         => '亲，请选择省份',
        'add'              => '亲，请填写地址',    
    ];

    protected $scene  = [       
        'scene_del'    =>  ['id_str'=>'require|checkcartid'],   
        'scene_add'    =>  ['title','name','tel','province','add'],   
    ];

    
}
