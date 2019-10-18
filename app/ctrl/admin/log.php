<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-17 15:51:17
 * Desc: 日志控制器
 */
namespace app\ctrl\admin;

use app\ctrl\admin\BaseController;
use app\model\log as logModel;
use app\model\admin as AdminModel;

class log extends BaseController{

	public $logM;
	public $admin_M;
	public function __initialize(){
		$this->logM = new logModel();	
		$this->admin_M = new AdminModel();
	}

	/*日志列表*/
	public function lists()
	{
		(new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
		$page_size = post('page_size',10);
		$where=[];
		$data  = $this->logM->lists($page,$page_size,$where);
		$count = $this->logM->new_count($where);
		$res['data'] = $data;
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page_size'] = $page_size;
        $res['page'] = $page;        
        return $res; 
	}


}