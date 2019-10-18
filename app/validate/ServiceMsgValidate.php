<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-26 16:12:21
 * Desc: 客服消息验证
 */

namespace app\validate;

class ServiceMsgValidate extends BaseValidate
{
    protected $rule = [
        'rating'    => 'require',
        'msg'  => 'require',
    ];

    protected $message = [
        'rating'    => '等级必须', 
        'msg'  => '消息必须', 
    ];

    protected $scene  = [
        'scene_add'        =>  ['rating','msg'],        
    ];





    

}
