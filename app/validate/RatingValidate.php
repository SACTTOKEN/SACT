<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 09:34:59
 * Desc: 用户验证
 */


namespace app\validate;

class RatingValidate extends BaseValidate
{
    protected $rule = [
        'title'    => 'require',
    ];


    protected $message = [
        'title'    => '标题必须',       
    ];


    protected $scene  = [
        'scene_add'   =>  ['title'=>'require'],
    ];

}
