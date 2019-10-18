<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-16 13:51:24
 * Desc: 定制类入口
 */

namespace app\ctrl\admin;

class made extends PublicController
{
    public function __initialize()
    { 

    }


    public function index()
    {
        (new \app\validate\MadeValidate())->goCheck();
        $cltrlclass = post('cltrlclass');
        $action = post('action');
        $made = c('made');
        if (!$made) {
            error('定制制度未开启', 404);
        }
        $ctrlfile = MADE . '/' . $made . '/ctrl/admin/' . $cltrlclass . '.php';
        if (!is_file($ctrlfile)) {
            error('查找不到类', 404);
        }
        $cltrlclass = '\made\\' . $made . '\ctrl\admin\\' . $cltrlclass;
        $made_C = new $cltrlclass();
        $res = $made_C->$action();
        return $res;
    }
}
