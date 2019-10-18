<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-02 10:09:25
 * Desc: 自定义excel表字段 控制器
 */

namespace app\ctrl\admin;

use app\model\excel_field as ExcelFieldModel;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;

class excel_field extends BaseController{
	
	public $field_M;
	public function __initialize(){
		$this->field_M = new ExcelFieldModel();
	}

    public function lists(){
    	$where = [];
    	$ar = $this->field_M->lists_all($where);
    	return $ar; 	
    }

	public function saveadd(){
		$iden = post('iden');
		$field_ar = ['a1','a2','a3','a4','a5','a6','a7','a8','a9','a10','a11','a12','a13','a14','a15','a16','a17','a18','a19','a20','a21','a22','a23','a24','a25','a26','a27','a28','a29','a30'];
		if(!in_array($iden, $field_ar)){
			error('标识不存在',404);
		}
		$where['iden'] = $iden;
		$is_have = $this->field_M->is_have($where);

		$data['con'] = post('con');
		$data['is_show'] = post('is_show');
		$data['is_list'] = post('is_list');

		if($is_have){
			$res = $this->field_M ->up_all($where,$data);
		}else{
			$data['iden'] = $iden;
			$res = $this->field_M -> save($data);
		}
		empty($res) && error('保存失败',404);
		admin_log('保存EXCEL标识',$id);   
	}

}