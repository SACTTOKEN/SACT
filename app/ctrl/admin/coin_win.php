<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-16 15:09:07
 * Desc: 币定盈
 */

namespace app\ctrl\admin;

use app\model\coin_win as CoinWinModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;

class coin_win extends BaseController{
	public $coin_win_M;
	public function __initialize(){
		$this->coin_win_M = new CoinWinModel();
	}


	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$uid = post('uid');
		$username = post('username');
		$nickname = post('nickname');
		$oid = post('oid');
		$stage_type = post('stage_type');

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
			$where['recharge_time[<>]'] = [$created_time_begin,$created_time_end];
		}

		if($oid){
			$where['oid[~]'] = $oid;
		}

		if($stage_type){
			$where['stage_type'] = $stage_type;
		}

		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->coin_win_M->lists($page,$page_size,$where);
		
		//cs($this->coin_wi_M->log(),1);

		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['uid_cn'] = $users['username'];  
			$one['nickname'] =$users['nickname'];   
			$one['avatar'] =$users['avatar'];   	
			$one['coin_rating_cn'] =$users['coin_rating_cn'];


			switch ($one['stage_type']) {
                case 'stage_7':
                    $win = renew_c('coin_win_stage_7');
                    $cycle = 7;
                break;
                
                case 'stage_30':
                    $win = renew_c('coin_win_stage_30');
                    $cycle = 30;
                break;

                case 'stage_50':
                    $win = renew_c('coin_win_stage_50');
                    $cycle = 50;                    
                break; 
               
            }
            $one['cycle'] = $cycle;
			$one['win'] = $win.'%'; 
		}

		$count = $this->coin_win_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        // var_dump($this->coin_wi_M->log());
        // exit();
        return $res; 
	}



	

}