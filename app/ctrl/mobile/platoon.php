<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 支付接口控制器
 */

namespace app\ctrl\mobile;

use app\validate\IDMustBeRequire;

class platoon extends BaseController{
	
	public $platoon_M;
	public function __initialize(){
		$this->platoon_M = new \app\model\platoon();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
        (new IDMustBeRequire())->goCheck();
        $data[1]=[$this->platoon_M->find($id)];
        if($data[1]){
            $users=user_info($data[1][0]['uid']);
            $data[1][0]['nickname']=$users['nickname'];
            $data[1][0]['username']=$users['username'];
            $data[1][0]['avatar']=$users['avatar'];

            $id=$data[1][0]['id'];
            for($i=2;$i<(c('platoon_layers')+1);$i++){
                $data_ar=$this->platoon_M->lists_all(['tid'=>$id]);
                if(empty($data_ar)){
                    break;
                }
                foreach($data_ar as &$vo){
                    $users=user_info($vo['uid']);
                    $vo['nickname']=$users['nickname'];
                    $vo['username']=$users['username'];
                    $vo['avatar']=$users['avatar'];
                }
                $data[$i]=$data_ar;
                $id=$this->platoon_M->lists_all(['tid'=>$id],'id');
                
            }
        }
        return $data;      
    }

    //根据用户查找点位
    public function lists()
    {
        $data['number']=$this->platoon_M->new_count(['uid'=>$GLOBALS['user']['id']]);
        $data['platoon']=(new \app\model\user())->find($GLOBALS['user']['id'],'platoon');
		$data['platoon_ar']=$this->platoon_M->lists_all(['uid'=>$GLOBALS['user']['id']]);
        return $data; 
    }

}