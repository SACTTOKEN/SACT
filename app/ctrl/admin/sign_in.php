<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-09 14:15:25
 * Desc: 签到控制器
 */

namespace app\ctrl\admin;

use app\model\sign_in as SignInModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;

class sign_in extends BaseController{
	
	public $sign_in_M;
	public function __initialize(){
		$this->sign_in_M = new SignInModel();
	}

	//修改签到奖励类型
	public function reward_type(){
		$help = post('help');
		$config_M = new \app\model\config();
		$where['cate'] = 'sign_get_jf';
		$data['help'] = $help;
		$res = $config_M-> up_all($where,$data);
		empty($res) && error('修改失败',400);
        return $res;
	}

	public function duty(){
        $configM = new \app\model\config();
        $data=$configM->lists_all('news_mission');

        foreach($data as $key=>$vo){
        	$data[$key]['is_open']=c('is_'.$vo['iden']);

            if(plugin_is_open('gasmrz')==0 && $vo['iden']=='wcsmrz'){
                unset($data[$key]);
            }

            if(plugin_is_open('imhyjsnt')==0 && $vo['iden']=='yhydycnt'){
                unset($data[$key]);
            }

            if($vo['iden']=='fxypwz' || $vo['iden']=='yhydycnt' || $vo['iden']=='cyycnts'){  //分享文章，参与聊天暂未开发
            	unset($data[$key]);
            }


        }

        return $data;
    }





}