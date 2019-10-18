<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 首次关注控制器
 */

namespace app\ctrl\admin;

use app\model\wx_follow as wx_follow_Model;
use app\ctrl\admin\BaseController;
use app\validate\WxFollowValidate;

class wx_follow extends BaseController{
	
	public $wx_follow_M;
	public function __initialize(){
		$this->wx_follow_M = new wx_follow_Model();
	}

    //修改
    public function edit(){
        $res = $this->wx_follow_M->find(1);
        return $res;
    }

    //保存修改 首次关注只有一条记录
    public function saveedit(){       
        (new WxFollowValidate())->goCheck(); 
        $data = post(['types','content','material']);
        $data['content'] = strip_tags($data['content']);    
        $is_have = $this->wx_follow_M->find(1);    
        if($is_have){
            $res = $this->wx_follow_M->up(1,$data);
        }else{
            $res = $this->wx_follow_M->save($data);
        }

        empty($res) && error('修改失败',400);
        admin_log('修改首次关注');    
        return $res;
    }


}