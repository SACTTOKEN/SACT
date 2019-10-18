<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: banner接口
 */
namespace app\ctrl\common;



class banner{

	/*添加banner*/
	public function index(){
		$res[] = $this->addbanner("限量秒杀","phnav","phnav_1","/resource/image/default/201901/eeff76ffe39d3075e31cbb9ac64a372f.jpg");
		$res[] = $this->addbanner("品牌特价","phnav","phnav_2","/resource/image/default/201901/eeff76ffe39d3075e31cbb9ac64a372f.jpg");
		$res[] = $this->addbanner("全球工厂","phnav","phnav_3","/resource/image/default/201901/cef0400c61669db229fe8bcf52d919b4.jpg");
		$res[] = $this->addbanner("超级秒杀","phnav","phnav_4","/resource/image/default/201901/ce4c2b8eab4d60ee2aac2525726036d7.jpg");
		$res[] = $this->addbanner("推币商城","phnav","phnav_5","/resource/image/default/201901/bd659745543c27b92a5c143a87ffa5d7.jpg");
		$res[] = $this->addbanner("9.9专区","phnav","phnav_6","/resource/image/default/201901/e1434a4fb56a019959af07ba5f3175fb.jpg");
		$res[] = $this->addbanner("老带新","phnav","phnav_7","/resource/image/default/201901/71c525566f4dc15ab7dbb368042db53e.jpg");
		$res[] = $this->addbanner("达人专享","phnav","phnav_8","/resource/image/default/201901/2aca7eb15063ad7f956cc443b37ce3a2.jpg");
		$res[] = $this->addbanner("免费领","phnav","phnav_9","/resource/image/default/201901/bfa7f1b8d28fa251198fb400c336793d.jpg");
		$res[] = $this->addbanner("推币折扣","phnav","phnav_10","/resource/image/default/201901/84a70cf7baa51c04d7c3b459934e68e0.jpg");
		return $res;
	}
	
	
	/*添加配置*/
	public function addbanner($title,$cate,$iden,$piclink){


		$bannerM = new \app\model\banner();
		
		if(!$bannerM->is_find($iden)){
			$data['title']   = $title;
			$data['iden']    = $iden;
			$data['cate']    = $cate;
			$data['piclink'] = $piclink;
			$res=$bannerM->save($data);
			return $res;
		}else{
			return true;
		}

		
		
	}
		
}