<?php
namespace app\validate;

class TourPersonValidate extends BaseValidate
{
    protected $rule = [
        'name'    => 'require', 
        'sfz'     => 'require|unique:tour_person', 
        'tel'     => 'require|isMobile',
    ];

    protected $message   = [
       'name' => '真实姓名必须',
       'sfz.require'  => '身份证必须',
       'sfz.unique'  => '身份证不能重复',
       'tel'  => '电话格式不正确',
    ];

    protected $scene  = [
        'scene_saveadd'     =>  ['name','sfz','tel'],
        'scene_saveedit'    =>  ['name','sfz'=>'require','tel'],
    ];

}
