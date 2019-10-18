<?php
/**
2. 增加汇总数据，可编辑 姓名电话  一个商户号，对应一个姓名电话
3. 增加商户，总额。就是针对商户号，计算他下面几张卡的总额


4. 按照总额排序
5. 增加按银行搜索，按姓名搜索

6. 数据管理页脚，增加金额总额
7. 数据汇总，增加一个自定义字段，扣减金额
8. 增加个根据数据备注栏
 */

namespace app\ctrl\admin;

use app\model\excel_auto as ExcelAutoModel;
use app\model\excel_auto2 as ExcelAuto2Model;
use app\model\excel_auto3 as ExcelAuto3Model;
use app\model\excel_auto4 as ExcelAuto4Model;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;

class excel_plus extends BaseController{
	public $auto_M;
	public $auto_2_M;
	public $auto_3_M;
	public $auto_4_M;
	public function __initialize(){
		$this->auto_M = new ExcelAutoModel();
		$this->auto_2_M = new ExcelAuto2Model();
		$this->auto_3_M = new ExcelAuto3Model();
		$this->auto_4_M = new ExcelAuto4Model();
	} 


//更新全部
public function save_auto4(){
	$ar = $this->auto_3_M->lists_all();
	foreach($ar as $one){
		unset($one['id']);
		unset($one['created_time']);
		unset($one['update_time']);
		unset($one['money_plus']);
		unset($one['bank_home']);
		$oid = $one['oid'];
		$is_have = $this->auto_4_M->have(['oid'=>$oid]);
		if($is_have){
			if($is_have['fix']==1){ //已手动修改的不复盖姓名,电话
				unset($one['title']);
				unset($one['tel']);

			}
			$res = $this->auto_4_M->up($is_have['id'],$one);
		}else{
			unset($one['money_plus']);
			unset($one['bank_home']);
			$res = $this->auto_4_M->save($one);
		}
	}
	//cs($this->auto_4_M->log(),1);
	empty($res) && error('更新失败',400);

	return $res;
}

	//更新姓名电话
	public function save_info(){
	$oid = post('oid');
	$title = post('title','');
	$tel = post('tel','');

	$remark_1 = post('remark_1','');
	$remark_2 = post('remark_2','');
	$remark_3 = post('remark_3','');


	if($title){
		$one['title'] = $title;
	}
	if($tel){
		$one['tel'] = $tel;
	}
	
		$one['remark_1'] = $remark_1;
		$one['remark_2'] = $remark_2;
		$one['remark_3'] = $remark_3;

	$is_have_3 = $this->auto_3_M->have(['oid'=>$oid]);
	if($is_have_3){		
		$this->auto_3_M->up($is_have_3['id'],$one);
	}

	$one['fix'] = 1;
	$is_have_4 = $this->auto_4_M->have(['oid'=>$oid]);
	if($is_have_4){
		$res = $this->auto_4_M->up($is_have_4['id'],$one);
		empty($res) && error('更新失败-', 404);
	}else{
		$one['oid'] = $oid;
		$res = $this->auto_4_M->save($one);
		empty($res) && error('更新失败!', 404);
	}
	
	return $res;
	}


}