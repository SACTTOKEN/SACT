<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: 二维码控制器
 */

namespace app\ctrl\mobile;
class app{
    //入口
    public function down(){
        $data['iphoneapp']=c('iphoneapp');
        $data['droidapp']=c('droidapp');
        $data['appdown_bj']=c('appdown_bj');
        return $data;
    }

}