<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: banner控制器
 */

namespace app\ctrl\mobile;

use app\model\banner as BannerModel;
use app\validate\BannerValidate;
use app\ctrl\mobile\BaseController;

class banner extends BaseController{
	
	public $bannerM;
	public function __initialize(){
		$this->bannerM = new BannerModel();
	}


    /*保存单张banner*/
	public function saveadd(){
		
		$data = post(['iden','title','cate','piclink','links','description','show','sort']);
		(new BannerValidate())->goCheck('scene_add');
		$res=$this->bannerM->save($data);
		empty($res) && error('添加失败',400);	 
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