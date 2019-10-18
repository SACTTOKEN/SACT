<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-30 09:37:32
 * Desc: 提币记录
 */

namespace app\ctrl\admin;

use app\model\transfer as transferModel;

class transfer extends BaseController{
	
	public $transfer_M;
	public function __initialize(){
		$this->transfer_M = new transferModel();
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$username = post('username');
		$nickname = post('nickname');
		$other_username = post('other_username');
		$other_nickname = post('other_nickname');
		$oid = post('oid');
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		$created_time_end = intval($created_time_end);
        
        $user_M = new \app\model\user();
   		if($username){
  			$where['uid'] = $user_M->find_mf_uid($username);
  		}

  		if($nickname){
  			$where['uid'] = $user_M->find_mf_uid_plus($nickname);
  		}
        
        if($other_username){
            $where['other_id'] = $user_M->find_mf_uid($other_username);
        }

        if($other_nickname){
            $where['other_id'] = $user_M->find_mf_uid_plus($other_nickname);
        }


  		if($created_time_begin){
  			$created_time_end = $created_time_end + 3600*24;
			$where['created_time[<>]'] = [$created_time_begin,$created_time_end];
		}

		if($oid){
			$where['oid[~]'] = $oid;
		}

		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->transfer_M->lists($page,$page_size,$where);

		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['uid_cn'] = $users['username'];  
			$one['nickname'] =$users['nickname']; 
			$one['coin_rating_cn'] =$users['coin_rating_cn'];
			if($one['status']==0){
				$one['status_cn'] = '未领取';
			}elseif($one['status']==1){
				$one['status_cn'] = '已完成';
			}else{
				$one['status_cn'] = '已退回';
            }
			if($one['types']==0){
				$one['types_cn'] = '互转';
			}else{
				$one['types_cn'] = '红包';
            }

            $users=user_info($one['other_id']);
            $one['other_uid_cn']=$users['username'];  
            $one['other_nickname']=$users['nickname'];  
            $one['other_coin_rating_cn']=$users['coin_rating_cn'];  

            $one['cate_cn']=find_reward_redis($one['cate']);
		}

		$count = $this->transfer_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
     
        return $res; 
	}



	

}