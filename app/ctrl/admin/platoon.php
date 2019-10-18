<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 支付接口控制器
 */

namespace app\ctrl\admin;


class platoon extends BaseController{
	
	public $platoon_M;
	public function __initialize(){
		$this->platoon_M = new \app\model\platoon();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
        if(empty($id)){
            $id=$this->platoon_M->have([],'id');
        }
        $data=$this->platoon_M->find($id);
        if($data){
            $users=user_info($data['uid']);
            $data['nickname']=$users['nickname'];
            $data['username']=$users['username'];
            $data['avatar']=$users['avatar'];

            $data['one']=$this->platoon_M->lists_all(['tid'=>$id]);
            if($data['one']){
              
            foreach($data['one'] as &$vo){
                $vo['two']=$this->platoon_M->lists_all(['tid'=>$vo['id']]);
                
                $users=user_info($vo['uid']);
                $vo['nickname']=$users['nickname'];
                $vo['username']=$users['username'];
                $vo['avatar']=$users['avatar'];

                if($vo['two']){
                    foreach($vo['two'] as &$vos){
                        $users=user_info($vos['uid']);
                        $vos['nickname']=$users['nickname'];
                        $vos['username']=$users['username'];
                        $vos['avatar']=$users['avatar'];
                        $vos['three']=$this->platoon_M->lists_all(['tid'=>$vos['id']]);
                        foreach($vos['three'] as &$voss){
                            $users=user_info($voss['uid']);
                            $voss['nickname']=$users['nickname'];
                            $voss['username']=$users['username'];
                            $voss['avatar']=$users['avatar'];
                        }
                    }
                }
            }
            }
        }
        return $data;      
    }

    //根据用户查找点位
    public function lists()
    {
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
        $username 			= post('username');
		$nickname			= post('nickname');
		$created_time_begin = post('created_time_begin');
        $created_time_end = post('created_time_end');
        $where=[];
		if ($username) {
			$user_M = new \app\model\user();
			$where['uid'] = $user_M->find_mf_uid($username);
		}
		if ($nickname) {
			$user_M = new \app\model\user();
			$where['uid'] = $user_M->find_mf_uid_plus($nickname);
		}
        if($created_time_begin>0){
			$created_time_end = $created_time_end ? $created_time_end : time();
			if(date("H:i:s",$created_time_end)=='00:00:00'){
				$created_time_end = $created_time_end + 3600 * 24;
			}
            $where['created_time[<>]'] = [$created_time_begin,$created_time_end];
        }

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$order=['id'=>'ASC'];
        $data=$this->platoon_M->lists_sort($page,$page_size,$where,$order);
        foreach($data as &$vo){
            $users=user_info($vo['uid']);
            $vo['nickname']=$users['nickname'];
            $vo['username']=$users['username'];
            $vo['avatar']=$users['avatar'];
        }
        $count = $this->platoon_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }

}