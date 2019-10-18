<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 限时抢购
 */

namespace app\ctrl\admin;

use app\model\rob_time as RobTimeModel;
use app\model\product as productModel;
use app\validate\RobTimeValidate;

class rob_time extends BaseController
{

	public $rob_time_M;
	public $productM;
	public function __initialize()
	{

		$this->rob_time_M = new RobTimeModel();
		$this->productM = new ProductModel();
	}


	/*添加时区*/
	public function saveadd()
	{
		(new RobTimeValidate())->goCheck('scene_add');
		$begin_time = post('begin_time');
		$end_time = post('end_time');

		$where['end_time[>=]'] = $begin_time;
		$is_have = $this->rob_time_M->is_have($where);
		if ($is_have) {
			error('时区有重叠的时间段', 400);
		}

		$data['begin_time'] = $begin_time;
		$data['end_time'] = $end_time;
		$res = $this->rob_time_M->save($data);
		//cs($this->rob_time_M->log(),1);
		empty($res) && error('添加失败', 400);
		return $res;
	}

	/*删除*/
	public function del()
	{
		(new \app\validate\DelValidate())->goCheck();
		$id_str = post('id_str');
		$id_ar = explode('@', $id_str);
		foreach ($id_ar as $vo) {
			$time_ar = $this->productM->is_have(['time_id' => $vo]);
			if ($time_ar) {
				error('已存在限时抢购商品无法删除', 404);
			}
			$res = $this->rob_time_M->del($vo);
			empty($res) && error('删除失败', 400);
		}
		return $res;
	}

	/*抢购时区列表*/
	public function lists()
	{
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$page = post("page", 1);
		$page_size = post("page_size", 10);
		$data = $this->rob_time_M->lists($page, $page_size, $where);
		$count = $this->rob_time_M->new_count($where);
		$res['all_num'] = $count;
		$res['all_page'] = ceil($count / $page_size);
		$res['page'] = $page;
		$res['data'] = $data;
		return $res;
	}

	public function lists_all()
	{
		$where['end_time[>]']=time();
		$data=$this->rob_time_M->lists_all($where,['id','begin_time','end_time']);
		return $data;
	}
}
