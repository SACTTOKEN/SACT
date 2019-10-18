<?php
namespace app\ctrl\mobile;


use app\model\new_duty as NewDutyModel;
use app\validate\NewDutyValidate;
use app\validate\IDMustBeRequire;

class new_duty extends BaseController{
	
	public $new_duty_M;
	public function __initialize(){	
		$this->new_duty_M  = new NewDutyModel();
	}

    public function reward(){
    	$uid = $GLOBALS['user']['uid'];
        $config_M = new \app\model\config();
        $data=$config_M->lists_all('news_mission');

        foreach($data as $key=>$rs){
        	if( renew_c('is_'.$rs['iden'])==0){
        		unset($data[$key]);
        	}
        }

        foreach($data as &$one){        	
			$one['help_cn'] = find_reward_redis($one['help']);
			$is_have = $this->new_duty_M->is_have(['uid'=>$uid,'iden'=>$one['iden']]);
			$one['is_done'] = $is_have;


			unset($one['id']);
			unset($one['types']);
			unset($one['yz']);
        }
        unset($one);
        return $data; 
    }



}