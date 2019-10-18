<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-08 11:44:34
 * Desc: 银行卡导入 根据卡号开头几位判定银行名
 */

namespace app\ctrl\admin;

use app\model\excel_bank_name as ExcelBankModel;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\PageValidate;
use app\validate\ExcelBankValidate;
use app\ctrl\admin\BaseController;

class excel_bank_name extends BaseController{
	
	public $bank_M;
	public function __initialize(){
		$this->bank_M = new ExcelBankModel();
	}

    public function lists(){
    	$where = [];
    	$ar = $this->bank_M->lists_all($where);
    	return $ar; 	
    }

	public function saveadd(){
		(new ExcelBankValidate())->goCheck();	
		$bank_name = post('bank_name');
		$begin_num = post('begin_num');	

		$where['begin_num'] = $begin_num;
		$is_have = $this->bank_M->is_have($where);
		$is_have && error('前几位号码已存在',400);

		$data['bank_name'] = $bank_name;
		$data['begin_num'] = $begin_num;
		$res = $this->bank_M->save($data);
		//cs($this->bank_M->log(),1);
		empty($res) && error('添加失败',400);
		return $res;
	}

	public function del(){
		(new \app\validate\DelValidate())->goCheck();
		$id_str = post('id_str');
        $id_ar = explode('@',$id_str);
		$res = $this->bank_M -> del($id_ar);
		empty($res) && error('删除失败',400);
		return $res;
	}


	public function saveedit(){
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id');	
		$begin_num = post('begin_num');
		$where['begin_num'] = $begin_num;
		$is_have = $this->bank_M->have($where);
		if(isset($is_have['id'])){
			if($is_have['id']!=$id){
			error('前几位号码已存在',400);
			}
		}

		$data['bank_name'] = post('bank_name');
		$data['begin_num'] = $begin_num;

		$res = $this->bank_M->up($id,$data);
		empty($res) && error('修改失败',400);
		return $res;
	}


	public function bank_config(){
		$field_ar = ['a1','a2','a3','a4','a5','a6','a7','a8','a9','a10','a11','a12','a13','a14','a15','a16','a17','a18','a19','a20','a21','a22','a23','a24','a25','a26','a27','a28','a29','a30'];
		
		$bank_name_iden = post('bank_name_iden');
		if(!in_array($bank_name_iden, $field_ar)){
			error('标识1不存在',404);
		}
		$begin_num_iden = post('begin_num_iden');
		if(!in_array($begin_num_iden, $field_ar)){
			error('标识2不存在',404);
		}

		$configM = new \app\model\config();

		$data_1['value'] = $bank_name_iden;		
		$res = $configM->up('bank_name_iden',$data_1);
		empty($res) && error('修改失败',400);

		$data_2['value'] = $begin_num_iden;
		$res = $configM->up('begin_num_iden',$data_2);
		empty($res) && error('修改失败',400);
		return $res;
	}

	public function bank_config_base(){
		$a1 = c('bank_name_iden');
		$a2 = c('begin_num_iden');
		$a1 = $a1 ? $a1 : '';
		$a2 = $a2 ? $a2 : '';
		$data['bank_name_iden'] = $a1;
		$data['begin_num_iden'] = $a2;
		return $data;
	}


	





}