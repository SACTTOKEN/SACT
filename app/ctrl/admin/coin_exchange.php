<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-30 09:37:32
 * Desc: 提币记录
 */

namespace app\ctrl\admin;

use app\model\coin_exchange as coin_exchangeModel;


class coin_exchange extends BaseController{
	
	public $coin_exchange_M;
	public function __initialize(){
		$this->coin_exchange_M = new coin_exchangeModel();
	}


	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$username = post('username');
		$nickname = post('nickname');
		$oid = post('oid');
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		$created_time_end = intval($created_time_end);

        if($username){
            $user_M = new \app\model\user();
           $where['uid'] = $user_M->find_mf_uid($username);
        }

        if($nickname){
                    $user_M = new \app\model\user();
                $where['uid'] = $user_M->find_mf_uid_plus($nickname);
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
		$data=$this->coin_exchange_M->lists($page,$page_size,$where);
		
		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['uid_cn'] = $users['username'];  
			$one['nickname'] =$users['nickname'];   
			$one['avatar'] =$users['avatar'];   
			$one['coin_rating_cn'] =$users['coin_rating_cn'];
			if($one['status']==1){
				$one['status_cn'] = '互转成功';
			}else{
				$one['status_cn'] = '申请中';
            }
            
            if($one['types']==1){
				$idne_CN=find_reward_redis($one['cate']);
                $one['cate_cn']=$idne_CN.'转ETH';
                $one['money']=$one['money'].$idne_CN;
                $one['fee']=$one['fee'].$idne_CN;
                $one['actual']=$one['actual'].'ETH';
            }else{
				$idne_CN=find_reward_redis($one['cate']);
                $one['cate_cn']='ETH转'.$idne_CN;
                $one['money']=$one['money'].'ETH';
                $one['fee']=$one['fee'].'ETH';
                $one['actual']=$one['actual'].$idne_CN;
            }
		}

		$count = $this->coin_exchange_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
    
        return $res; 
	}



	

}