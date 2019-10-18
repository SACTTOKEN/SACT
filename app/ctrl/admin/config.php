<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 配置类
 */

namespace app\ctrl\admin;

use app\validate\IDMustBeRequire;
use app\model\config as ConfigModel;
use app\validate\ConfigValidate;
use app\ctrl\admin\BaseController;
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
				$ar_one = $this->configM->find($key,'*');

				if($ar_one['yz']==1 && $val!=''){
				error($ar_one['title'].' 该配置不能为空');
				}

				if($ar_one['yz']==2 && !is_numeric($val) && $val>=0){
				error($ar_one['title'].' 该配置只能为数字');
				}

				if($cate){
					$up_data = ['value'=>$val,'cate'=>$cate];
				}else{
					$up_data = ['value'=>$val];
				}

				if($cate == "news_mission"){
					$help = $this->configM->find($key,'help');
					if(!$help){
						$up_data['help'] = 'integral'; //新手任务默认积分
					}
				}

				$back = $this->configM->up($key,$up_data);
				!$back && $res=false;
				$redis_key = 'config:'.$key;
				$this->redis->set($redis_key,$val);				
		}
		
		admin_log('修改配置',$cate);  
		return $res;
	}

	
	/*保存单条配置*/
	public function saveadd(){
		(new ConfigValidate())->goCheck('scene_add');
		$data = post(['iden','title','help','value','type','cate']);


		$this->check_iden($data['iden'],$data['value']);
		
		$res=$this->configM->save($data);
		
		$key = 'config:'.$data['iden'];
		$val = $data['value'];

        $this->redis->set($key,$val);
		empty($res) && error('添加失败',400); 
		admin_log('修改配置',$data['iden']);  
		return $res;
	}


	/*配置值逻辑判断*/
	public function check_iden($iden,$value){
		if($iden=='gbwzsj'){ //网站定时关闭
			$kqwzsj = renew_c('kqwzsj');
			if($kqwzsj >= $value ){
				error('关闭时间要大于开启时间',400);
			}
		}

		if($iden =='ye_gbtxsj'){ //早起签到结束时间
			$ye_kqtxsj = renew_c('ye_kqtxsj');
			if($ye_kqtxsj >= $value ){
				error('关闭时间要大于开启时间',400);
			}
		}

	}


	/*修改单条配置*/
	public function saveedit(){

		(new ConfigValidate())->goCheck('scene_find');
		$data['value'] = post('value');
		$iden = post('iden');

		$this->check_iden($iden,$data['value']);

		$ar = $this->configM->find($iden,'*');

		if($ar['yz']==1 && !($data['value'])){
			error($ar['title'].' 该配置不能为空');
		}
		
		if($ar['yz']==2 && !is_numeric($data['value'])){
			error($ar['title'].' 该配置只能为数字');
		}
		
		$res = $this->configM->up($iden,$data);
		renew_c($iden);	
		empty($res) && error('修改失败',400); 

		if($iden=='wxid' || $iden=='appid' || $iden=='appsec' || $iden=='wxhdwz'){
			$redis = new \core\lib\redis();
			$key = 'test_access_token';
			$redis->del($key);
		}

		admin_log('修改配置',$data);
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

	/*配置排序*/
	public function sort(){
		$sort_str = post('sort_str');
		$cate = post('cate');
		$ar = [];
		if(!empty($sort_str)){
			$ar = explode('@',$sort_str);
		}
		empty($ar) && error('排序失败',400);
		
		$ar = array_reverse($ar);
		$res = $this->configM->sort($ar,$cate);
		empty($res) && error('排序失败',400);
		return $res;
	}


	/*关闭或开启单条配置 is_open=0*/
	public function config_open(){
		$is_open = post('is_open');
		$iden = 'is_'.post('iden');
		//(new ConfigValidate())->goCheck('scene_config_open');
		$data['value'] = $is_open;
		$res = $this->configM->up($iden,$data);
		renew_c($iden);
		empty($res) && error('操作失败',400);
		return $res;
	}


	/*配置项改变金额类型*/
	public function change_money(){
		$help = post('help');
		$iden = post('iden');
		$data['help'] = $help;
		$res = $this->configM->up($iden,$data);
		empty($res) && error('操作失败',400);
		return $res;
	}

	

		
}