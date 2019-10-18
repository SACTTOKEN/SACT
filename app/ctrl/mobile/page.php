<?php 
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-08 10:28:15
 * Desc: 首页控
 */
namespace app\ctrl\mobile;

use app\model\user;

class page{

	public $page_S;
	public function __initialize(){
		$this->page_S = new \app\service\page();
	}


	public function index()
	{
		(new \app\validate\BannerPageValidate())->goCheck('iden');
        $iden=post('iden');
        $data=$this->page_S->redis_page($iden);
        return $data;
	}
	
	public function vip()
	{
		$data['vip_page_background']=c('vip_page_background');
        $data['vip_page_background_color']=c('vip_page_background_color');
		$data['info']['product']['pro']=(new \app\model\product())->page('vip');
		$data['username']=$GLOBALS['user']['username'];
		$data['nickname']=$GLOBALS['user']['nickname'];
		$data['avatar']=$GLOBALS['user']['avatar'];
        $tid_cn = (new user())->find($GLOBALS['user']['tid'],['nickname','username','avatar']); //推荐人用户名
        if(!(empty($tid_cn['nickname']) || empty($tid_cn['avatar']))){
			$data['nickname']=$tid_cn['nickname'];
			$data['avatar']=$tid_cn['avatar'];
		}
        return $data;
	}

	public function cate()
	{
		$data['cate']=(new \app\service\product)->find_tree();
		$data['types']=c('shop_class');
		return $data;
	}

	public function announcement()
	{
		$data=(new \app\model\config())->list_cate('announcement');
		$data=array_column($data, null, 'iden');
		return $data;
	}
	/* 
    public function home(){
        $config_M = new \app\model\config();
		$ar = $config_M->list_cate('homepage');
		$data = [];
		if(!empty($ar)){
			foreach($ar as $one){
				$top = 'top_'.$one['iden'];
				$data[$top]['cate'] = $one['iden'];
				$data[$top]['show'] = $one['value'];
				if($one['iden']=='head'){
					$data[$top]['title'] = $one['value'];
				}else{
					$data[$top]['title'] = $one['title'];
				}
				
				$data[$top]['data'] = (new \app\model\banner())->list_cate($one['iden']);
			}
		}
		$data['product_cate'] = (new \app\model\product_cate())->tree(0,['id','title']);
		//商品系 求shop_show为1,和 day_show为1的商品	
		$pro_M = new \app\model\product();
		$shop_data = $pro_M ->homepage_pro('shop_show'); 
		$day_data  = $pro_M ->homepage_pro('day_show'); 
		$data['top_shoplist']['data'] = $shop_data;
		$data['top_day']['data'] = $day_data;
		return $data;
	} */
}