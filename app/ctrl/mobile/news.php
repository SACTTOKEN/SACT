<?php
namespace app\ctrl\mobile;

use app\model\news as NewsModel;
use app\model\news_cate as NewsCateModel;
use app\validate\IDMustBeRequire;

class news{
	
	public $newsM;
	public $news_cate_M;
	public function __initialize(){
		$this->newsM  = new NewsModel();
		$this->news_cate_M  = new NewsCateModel();
	}


	/*分类*/
	public function news_cate()
	{
		$where['show']=1;
		$where['ORDER'] = ['sort'=>'DESC','id'=>'ASC'];
		$res = $this->news_cate_M->lists_all($where);
		/* foreach($res as &$vo){
			$vo['vue_type']=$vo['is_collection'];
		} */
		return $res;
	}
	
	
	/*前端某一类新闻*/
	public function news_lists()
	{
		(new \app\validate\PageValidate())->goCheck();
		$where = [];			
		$cate_id = post('cate_id');
		if($cate_id){
			$where['cate_id'] = $cate_id;
		}	
		$where['show'] = 1;
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$order=[
			'is_top'=>'DESC',
			'sort'=>'DESC',
			'id'=>'DESC',
		];
		$data=$this->newsM->lists_sort($page,$page_size,$where,$order);

        $res['data'] = $data; 
        return $res; 
	}


	//新闻详情
	public function news_info()
	{
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$res = $this->newsM->have(['id'=>$id,'show'=>1]);
		empty($res) && error('新闻不存在',10007);
		$res['cate_cn'] = $this->news_cate_M->find($res['cate_id'],'title'); //中文分类
		$res['content'] = str_replace('@link=@',' style="width: 100%;" src=',$res['content']);
		$this->newsM->up($id,['hit[+]'=>1]);

		$uid=$GLOBALS['user']['id'];
		$where['uid']=$uid;
		$where['aid']=$id;
		$collect = (new \app\model\news_collect())->is_have($where);
		$res['collect']=$collect;
		return $res;
	}


	//推荐新闻
	public function news_tj()
	{
		(new \app\validate\PageValidate())->goCheck();
		$where = [];				
		//$where['cate_id'] =(new \app\model\news_cate())->lists_all(['is_collection'=>0],'id');
		$data=$this->newsM->lists_tj();
		//cs($this->newsM->log(),1);
        $res['data'] = $data; 
        return $res; 
	}

	


}