<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 首次关注验证
 */

namespace app\validate;

class WxFollowValidate extends BaseValidate
{
    protected $rule = [
        'types'    => 'require',
    ];


}
