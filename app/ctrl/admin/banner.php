<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: banner控制器
 */

namespace app\ctrl\admin;

use app\model\banner as BannerModel;
use app\validate\BannerValidate;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use core\lib\redis;

class banner extends BaseController{
	
	public $bannerM;
	public $redis;
	public function __initialize(){
		$this->bannerM = new BannerModel();
		$this->redis = new redis();
	}



    /*保存单张banner*/
	public function saveadd(){
		
		$data = post(['iden','title','cate','piclink','links','description','show','sort']);
		(new BannerValidate())->goCheck('scene_add');
		$res=$this->bannerM->save($data);
		empty($res) && error('添加失败',400);	 
		
		admin_log('添加广告',$res);  
		return $res;
	}


	/*读取某类banner*/
	public function find_by_cate(){
		$cate = post('cate');
		$data = $this->bannerM->list_cate($cate);
		foreach($data as $one){
			$ar[] = $one['piclink'];
		}	
		return $ar;
	}

	/*查某类banner,参数cate分类标识*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		$cate = post('cate');
		(new BannerValidate())->goCheck('scene_list');
		$data=$this->bannerM->lists_all($cate);
        return $data; 
	}

	/*按iden删除banner*/
	public function del(){
		$iden = post('iden');
		(new BannerValidate())->goCheck('scene_find');
		$res=$this->bannerM->del($iden);
		empty($res) && error('删除失败',400);		
		admin_log('删除广告',$iden);  
		return $res;
	}

	/*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->bannerM->find($id);
		empty($res) && error('数据不存在',404); 
		return $res;
	}

	/*按iden修改banner*/
	public function saveedit()
	{	
		$iden = post('iden');
    	(new BannerValidate())->goCheck('scene_find');
    	$data = post(['title','cate','piclink','links','description','show','sort']);
		$res=$this->bannerM->up($iden,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改广告',$iden);  
 		return $res; 
	}


	/**页设置轮播图 
	注：banner表的cate 是config表的iden,全部归属于config表的cate字段为homepage下
	*/
	public function save_homepage(){
		$flag=true;
		$top_del = post('top_del');
		$top_del = json_decode($top_del,true);
		$top_banner = post('top_banner'); //轮播图	
		$top_banner = json_decode($top_banner,true);
		$top_brand = post('top_brand'); //品牌精选
		$top_brand = json_decode($top_brand,true);
		
		$top_find = post('top_find'); //发现品牌
		$top_find = json_decode($top_find,true);
		$top_head = post('top_head'); //商城主页
		$top_head = json_decode($top_head,true);
		$top_list = post('top_list'); //推荐榜单
		$top_list = json_decode($top_list,true);
		$top_menu = post('top_menu'); //首页菜单
		$top_menu = json_decode($top_menu,true);

		$top_shoplist = post('top_shoplist'); //推荐商品
		$top_shoplist = json_decode($top_shoplist,true);
		$top_day = post('top_day'); //天天惊喜
		$top_day = json_decode($top_day,true);


		$top_adv = post('top_adv');    //广告位
		$top_adv = json_decode($top_adv,true);


		$this->add_hp($top_banner);
		$this->add_hp($top_brand);


		$this->add_hp($top_find);
		$this->add_hp($top_head);
		$top_logo['cate']='logo';
		$top_logo['show']=$top_head['data'][0]['piclink'];
		$this->add_hp($top_logo);
		$this->add_hp($top_list);
		$this->add_hp($top_menu);
		$this->add_hp($top_shoplist);
		$this->add_hp($top_day);
		$this->add_hp($top_adv);

		//一类是banner系,一类是商品系
		if(!empty($top_shoplist['data'])){
			$this->add_pro($top_shoplist['data'],'shop_show');
		}
		if(!empty($top_day['data'])){
			$this->add_pro($top_day['data'],'day_show');
		}

		$pro_M = new \app\model\product();

		//所有的删除
		if(!empty($top_del)){
			$this->del_hp($top_del['del_top_banner']);
			$this->del_hp($top_del['del_top_brand']);
			$this->del_hp($top_del['del_top_find']);
			$this->del_hp($top_del['del_top_head']);
			$this->del_hp($top_del['del_top_list']);
			$this->del_hp($top_del['del_top_menu']);
			$this->del_hp($top_del['del_top_adv']);
			$this->del_pro($top_del['del_top_shoplist'],'shop_show');
			$this->del_pro($top_del['del_top_day'],'day_show');
		}

		admin_log('修改首页模板');  
		return $flag;
		
	}




	

	public function add_pro($ar,$field){
		$res = true;
		$pro_M = new \app\model\product();
		if(!empty($ar)){
			foreach($ar as $one){
				if($one>0){
					$data[$field] = 1; //1:homepage首页显示 0：不显示。默认0
					$res = $pro_M->up($one,$data);
				}
				
			}

		}			
		return $res;			
	}

	public function del_pro($ar,$field){
		$res = true;
		$pro_M = new \app\model\product();
		if(!empty($ar)){
			foreach($ar as $one){
				if($one>0){
					$data[$field] = 0; //1:homepage首页显示 0：不显示。默认0
					$res = $pro_M->up($one,$data);
				}
				
			}

		}			
		return $res;
	}

	public function del_hp($ar){
		$res = true;
		if(!empty($ar)){
			$res = $this->bannerM->del_byid($ar);
		}		
		return $res;
	}


	public function add_hp($ar){
		$flag = true;
		$config_M = new \app\model\config();

		if(!empty($ar['cate'])){
				$is_have = $config_M->is_find_bycate($ar['cate'],'homepage');
				if($is_have){
					if($ar['cate']=='head'){
						$data_up['value'] = $ar['title'];
					}else{
						$data_up['title'] = $ar['title'];
						$data_up['value'] = $ar['show'];
					}
					$config_M->up($ar['cate'],$data_up);
					$key = 'config:'.$ar['cate'];
					$this->redis->set($key,$data_up['value']);
				}else{
					$data_save['title'] = $ar['title'];
					$data_save['value'] = $ar['show'];
					$data_save['iden'] = $ar['cate'];
					$data_save['cate'] = 'homepage';
					$config_M->save($data_save);
				}
				if(isset($ar['data'])){
				foreach($ar['data'] as $one){
					if(isset($one['piclink'])){
					$data_banner['piclink'] = $one['piclink'];
					$data_banner['links'] = $one['links'];
					$data_banner['cate']  =  $ar['cate'];
					$data_banner['title'] = $one['title'];

					// $price = 0;
					// if(isset($one['price'])){
					// 	$price = $one['price'];
					// }
					// $data_banner['price'] = $price;
			
					if($one['id']>0){
						$res = $this->bannerM->up_byid($one['id'],$data_banner);
						if(empty($res)){$flag = false;}
					}else{
						$res = $this->bannerM->save($data_banner);
						if(empty($res)){$flag = false;}
					}

					}
				}
			}

			}
			return $flag;
	}
  // JOSN格式示例
  // {
  //           "cate": "banner",
  //           "show": "1",
  //           "title": "首页轮播图",
  //           "data": [
  //               {
  //                   "id": 0,
  //                   "piclink": "/assets/img/banner_template.9d793119.png",
  //                   "links": "",
  //                   "title": "图一"
  //               },
  //               {
  //                   "id": 0,
  //                   "piclink": "/assets/img/banner_template.9d793119.png",
  //                   "links": "",
  //                   "title": "图二"
  //               }
  //           ]
  // }    

	public function homepage(){
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
				
				$data[$top]['data'] = $this->bannerM->list_cate($one['iden']);
			}
		}

		//商品系 求shop_show为1,和 day_show为1的商品	
		$pro_M = new \app\model\product();
		$shop_data = $pro_M ->homepage_pro('shop_show'); 
		$day_data  = $pro_M ->homepage_pro('day_show'); 

		$data['top_shoplist']['data'] = $shop_data;
		$data['top_day']['data'] = $day_data;

		return $data;
	}



	public function ewm_bg(){
		$json = post('ar');
		empty(is_json($json)) && error('数据不是JSON',400);
		$ar = json_decode($json,true);	
		empty($ar) && error('请上传二维码背景',400);
	
		$cate = post('cate');
		
		$this->bannerM->del_by_cate('ewm_bg');

		$i = 1;
		foreach($ar as $one){
			$data['piclink'] = $one;
			$data['cate'] = $cate;
			$data['title'] = '二维码背景图';
			$data['iden'] = 'ewm_bg_'.$i;
			$res=$this->bannerM->save($data);
			$i++;
		}

		empty($res) && error('添加失败',400);	 
		
		admin_log('添加二维码背景图',$res);  
		return $res;
	}



	public function ewm_bg_list(){
		$cate = post('cate');
		$data = $this->bannerM->list_cate($cate);

		foreach($data as $one){
			$ar[] = $one['piclink'];
		}	
		return $ar;
	}








	


}