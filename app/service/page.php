<?php
/**
 * Created by yaaaaa__god 
 * User: yaaaaa
 * Date: 2018-12-14 11:19:11
 * Desc: 页面
 */
namespace app\service;
use app\model\banner as bannerModel;
use app\model\page as pageModel;
use app\model\product as productModel;
use core\lib\redis;
class page{
	public $banner_M;
	public $page_M;
	public $product_M;
	public $redis;
    public function __construct()
    {
		$this->banner_M = new bannerModel();
		$this->page_M = new pageModel();
		$this->product_M = new productModel();
		$this->redis = new redis();
    }

    public function redis_page($iden)
    {
        $key = 'page:'.$iden;
        $is_have = $this->redis->exists($key);
        if($is_have){
            $data = $this->redis->get($key);
        }
        if(!$data){
            $info=$this->page_M->lists_all(['iden'=>$iden,'types[!]'=>'module'],['id','iden','is_show','title','title_en','style','types']);
            if(empty($info)){
               $info=$this->page_M->copy($iden);
            }
            foreach($info as &$vo){
                if($vo['types']!='product'){
                    $vo['banner']=$this->banner_M->list_page($vo['id']);
                }
            }
            $data['info']=array_column($info,null,'types');
            $module=$this->page_M->lists_all(['iden'=>$iden,'types'=>'module'],['id','iden','is_show','title','title_en','style','types']);
            foreach($module as &$vo){
                $vo['banner']=$this->banner_M->list_page($vo['id']);
                $vo['part_show']=false;
            }
            $data['module']=$module;
            if(empty($data) && $data!=0){
                error($iden.'数据不存在',404);
            }
            $this->redis->set($key,$data);
        }
        $data['info']['product']['pro']=$this->product_M->page($iden);
        $data['head']=c('head');
        $data['logo']=c('logo');
		$data['product_cate'] = (new \app\model\product_cate())->tree(0,['id','title']);
        return $data;
    }

    public function renew_page($iden)
    {
        $key = 'page:'.$iden;
        $info=$this->page_M->lists_all(['iden'=>$iden,'types[!]'=>'module'],['id','iden','is_show','title','title_en','style','types']);
        foreach($info as &$vo){
            if($vo['types']!='product'){
                $vo['banner']=$this->banner_M->list_page($vo['id']);
            }
        }
        $data['info']=array_column($info,null,'types');
        $module=$this->page_M->lists_all(['iden'=>$iden,'types'=>'module'],['id','iden','is_show','title','title_en','style','types']);
        foreach($module as &$vo){
            $vo['banner']=$this->banner_M->list_page($vo['id']);
            $vo['part_show']=false;
        }
        $data['module']=$module;
        if(empty($data) && $data!=0){
            error($iden.'数据不存在',404);
        }
        $this->redis->set($key,$data);
    }



    public function redis_supplier_page()
    {
        $iden='supplier';
        $key = 'page:'.$iden;
        $is_have = $this->redis->exists($key);
        if($is_have){
            $data = $this->redis->get($key);
        }
        if(!$data || 1==1){
            $info=$this->page_M->lists_all(['iden'=>$iden],['id','iden','is_show','title','title_en','style','types']);
            if(empty($info)){
               $info=$this->page_M->copy($iden);
            }
            foreach($info as &$vo){
                if($vo['types']!='merchant'){
                    $vo['banner']=$this->banner_M->list_page($vo['id']);
                }
            }
            $data['info']=array_column($info,null,'types');
            $data['info']['merchant']['pro']=(new \app\model\user_attach())->lists_supplier(['shop_is_home'=>1]);
            if(empty($data) && $data!=0){
                error($iden.'数据不存在',404);
            }
            $this->redis->set($key,$data);
        }
        if(isset($data['info']['merchant']['pro'])){
            foreach($data['info']['merchant']['pro'] as &$vo){
                $users=user_info($vo['id']);
                $vo['shop_title']=$users['shop_title'];
                $vo['shop_logo']=$users['shop_logo'];
                $vo['distance']=0;
                $vo['product_number']=$this->product_M->new_count(['sid'=>$vo['id'],'show'=>1,'is_check'=>1]);
                $where['sid']=$vo['id'];
                $where['show']=1;
                $where['is_check']=1;
                $where['is_check']=1;
                $where['LIMIT'] = [0,3];
                $where['ORDER'] = ['id'=>'DESC'];
                $vo['product_lists']=$this->product_M->supplier_lists(['sid'=>$vo['id'],'show'=>1,'is_check'=>1,'LIMIT'=>[0,3]]);
            }
        }
        
        return $data;
    }

    
    public function renew_supplier_page()
    {
        $iden='supplier';
        $key = 'page:'.$iden;
        $info=$this->page_M->lists_all(['iden'=>$iden],['id','iden','is_show','title','title_en','style','types']);
        foreach($info as &$vo){
            if($vo['types']!='merchant'){
                $vo['banner']=$this->banner_M->list_page($vo['id']);
            }
        }
        $data['info']=array_column($info,null,'types');$data['info']['merchant']['pro']=(new \app\model\user_attach())->lists_supplier(['shop_is_home'=>1]);
        if(empty($data) && $data!=0){
            error($iden.'数据不存在',404);
        }
        $this->redis->set($key,$data);
    }
}