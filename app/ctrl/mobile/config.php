<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 配置类
 */
namespace app\ctrl\mobile;

use app\model\config as ConfigModel;
use app\validate\ConfigValidate;
use core\lib\redis;
class config extends BaseController{

	public $configM;
	public $redis;
	public function __initialize(){
		$this->configM = new ConfigModel();	
		$this->redis = new redis();
	}

	/*某一类的相关配置*/
	public function lists()
	{
		$cate = post('cate');
		(new ConfigValidate())->goCheck('scene_list');
		$data=$this->configM->lists_all($cate);
        return $data; 
	}

	public function find_iden(){
		$iden = post('iden');
		$data=$this->configM->find($iden);
		return $data;
	}


	/*某一类的配置修改保存,只根据iden改value的值,ar为json数组，iden为key,value为val*/
	public function savelist(){

		$ar = json_decode(post('ar'),true);
		$cate = post('cate');
		(new ConfigValidate())->goCheck('scene_savelist');
		$res = true;
		foreach($ar as $key=>$val){
				if($cate){
					$up_data = ['value'=>$val,'cate'=>$cate];
				}else{
					$up_data = ['value'=>$val];
				}
				$back = $this->configM->up($key,$up_data);
				!$back && $res=false;
				$redis_key = 'config:'.$key;
				$this->redis->set($redis_key,$val);				
		}
		return $res;
	}


	/*如果要启动代付 需先判断代付有无配置*/
	public function check_df(){
		(new ConfigValidate())->goCheck('scene_find');

		$pay_M = new \app\model\pay();

		$check_iden = post('iden');
		if(post('value')==1){
			if($check_iden=='ye_zfbdfjk' || $check_iden=='jf_zfbdfjk' || $check_iden=='yj_zfbdfjk'){
				$ar = $pay_M->find_by_title('支付宝代付');
				empty($ar['show']) && error('请先到支付设置进行代付配置',400); 
			}

			if($check_iden=='ye_wxdfjk' || $check_iden=='jf_wxdfjk' || $check_iden=='yj_wxdfjk'){
				$ar = $pay_M->find_by_title('微信代付');
				empty($ar['show']) && error('请先到支付设置进行代付配置',400); 
			}
		}
		return true;

	}


		
}