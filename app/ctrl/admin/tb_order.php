<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-30 13:45:57
 * Desc: 淘宝客订单控制器 因SDK不支持,暂停
 */

namespace app\ctrl\admin;

use app\model\tb_order as TbOrderModel;

use app\validate\TbOrderValidate;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;

use app\validate\AllsearchValidate;

class tb_order extends BaseController{
	
	public $tb_order_M;

	public function __initialize(){
		$this->tb_order_M  = new TbOrderModel();	
	}


	/*订单列表*/
	public function lists()
	{
		(new AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];	
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->tb_order_M->lists($page,$page_size,$where);
		$count = $this->tb_goods_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}


    /*采集 订单*/
    // public function renew_order(){
    //     require_once(IMOOC."/extend/taobao/TopSdk.php");
    //     $c = new \TopClient;
    //     $c->appkey =  "26041159";
    //     $c->secretKey = "06fdc1cec3abd264ab14caf53d61a087"; 
    //     $req = new \TbkOrderDetailsGetRequest;

    // }

}