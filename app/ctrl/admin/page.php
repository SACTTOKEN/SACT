<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: banner控制器
 */

namespace app\ctrl\admin;

use app\model\banner as bannerModel;
use app\model\page as pageModel;
use app\model\product as productModel;
use app\validate\BannerPageValidate;

class page extends BaseController{
	
	public $banner_M;
	public $page_M;
	public $product_M;
	public $page_S;
	public function __initialize(){
		$this->banner_M = new bannerModel();
		$this->page_M = new pageModel();
		$this->product_M = new productModel();
		$this->page_S = new \app\service\page();
		$this->configM = new \app\model\config();
	}

    //返回数据
    public function index()
    {
		(new BannerPageValidate())->goCheck('iden');
        $iden=post('iden');
        //$this->page_S->renew_page($iden);
        $data=$this->page_S->redis_page($iden);
        $data['footer']=$this->banner_M->list_cate('footer');
        return $data;
    }

    //vip数据
    public function vip()
    {
        $data['vip_page_background']=c('vip_page_background');
        $data['vip_page_background_color']=c('vip_page_background_color');
        $data['info']['product']['pro']=$this->product_M->page('vip');
        return $data;
    }

    /*提交保存*/
	public function saveedit(){
		(new BannerPageValidate())->goCheck('scene_add');
		$data = post(['iden','head','logo','background','slide','navigation','product','module','del_module','del_banner','footer']);
        //logo
        $this->configM->up('head',['value'=>$data['head']]);
        $this->configM->up('logo',['value'=>$data['logo']]);
        renew_c('head');	
        renew_c('logo');	
        //底部导航
        if(isset($data['footer'])){
            $footer=json_decode($data['footer'],true);
            if(count($footer)>0){
            foreach($footer as $vo){
                if(isset($vo['id']) && $vo['id']>0){
                    $banner=array();
                    $banner['title']=$vo['title'];
                    $banner['piclink']=$vo['piclink'];
                    $banner['links']=$vo['links'];
                    $this->banner_M->up_byid($vo['id'],$banner);
                }else{
                    $banner=array();
                    $banner['title']=$vo['title'];
                    $banner['piclink']=$vo['piclink'];
                    $banner['links']=$vo['links'];
                    $banner['cate']='footer';
                    $this->banner_M->save($banner);
                }
            }
            }
        }

        //固定模块
        $info=$this->page_M->lists_all(['iden'=>$data['iden'],'types[!]'=>'module']);
        $info=array_column($info,null,'types');
        foreach($info as $key=>$vo){
            if(isset($data[$key])){
            $ar=[];
            $ar=json_decode($data[$key],true);
            if(isset($ar)){
            $page_ar['title']=$ar['title'];
            $page_ar['title_en']=$ar['title_en'];
            $page_ar['is_show']=$ar['is_show'];
            $this->page_M->up($vo['id'],$page_ar);
            if(isset($ar['banner'])){
                foreach($ar['banner'] as $vos){
                    if($vos){
                    if(isset($vos['id']) && $vos['id']>0){
                        $banner=array();
                        $banner['title']=$vos['title'];
                        $banner['piclink']=$vos['piclink'];
                        $banner['links']=$vos['links'];
                        $banner['description']=$vos['desc'];
                        $this->banner_M->up_byid($vos['id'],$banner);
                    }else{
                        $banner=array();
                        $banner['title']=$vos['title'];
                        $banner['piclink']=$vos['piclink'];
                        $banner['links']=$vos['links'];
                        $banner['description']=$vos['desc'];
                        $banner['aid']=$vo['id'];
                        $this->banner_M->save($banner);
                    }
                    }
                }
            }
            }
            }
        }
        //活动模块
        $module=json_decode($data['module'],true);
        foreach($module as &$vo){
            if(isset($vo['id'])  && $vo['id']>0){
                $page_ar['title']=$vo['title'];
                $page_ar['title_en']=$vo['title_en'];
                $page_ar['is_show']=$vo['is_show'];
                $page_ar['style']=$vo['style'];
                $this->page_M->up($vo['id'],$page_ar);
            }else{
                $page_ar['title']=$vo['title'];
                $page_ar['title_en']=$vo['title_en'];
                $page_ar['is_show']=$vo['is_show'];
                $page_ar['style']=$vo['style'];
                $page_ar['iden']=$data['iden'];
                $page_ar['types']='module';
                $vo['id']=$this->page_M->save($page_ar);
            }
            if(isset($vo['banner'])){
            foreach($vo['banner'] as $vos){
                if($vos){
                    if(isset($vos['id']) && $vos['id']>0){
                        $banner=array();
                        $banner['title']=$vos['title'];
                        $banner['piclink']=$vos['piclink'];
                        $banner['links']=$vos['links'];
                        $banner['description']=$vos['desc'];
                        $this->banner_M->up_byid($vos['id'],$banner);
                    }else{
                        $banner=array();
                        $banner['title']=$vos['title'];
                        $banner['piclink']=$vos['piclink'];
                        $banner['links']=$vos['links'];
                        $banner['description']=$vos['desc'];
                        $banner['aid']=$vo['id'];
                        $this->banner_M->save($banner);
                    }
                }
                }
            }
        }
        //删除del_module模块
        if(isset($data['del_module'])){
        $del_module = explode('@',$data['del_module']);
		$where['id']=$del_module;
		$where['iden']=$data['iden'];
		$where['types']='module';
        $page_ar=$this->page_M->lists_all($where,'id');
        foreach($page_ar as $vo){
            $this->banner_M->del_all(['aid'=>$vo]);
        }
        $this->page_M->del_all($where);
        }
        //删除del_banner广告
        if(isset($data['del_banner'])){
        $del_banner = explode('@',$data['del_banner']);
		$abnner_where['id']=$del_banner;
        $this->banner_M->del_all($abnner_where);
        }
        $this->page_S->renew_page($data['iden']);
		admin_log('修改页面',$data['iden']);  
		return "修改成功";
    }

    public function add_product()
    {
		(new BannerPageValidate())->goCheck('iden');
        (new \app\validate\IDMustBeRequire())->goCheck();
        $parameter=post(['iden','id']);
        switch ($parameter['iden'])
        {
        case 'vip':
            $data['types']=1;
            break; 
        default:
        }
        $data['iden']=$parameter['iden'];
        $this->product_M->up($parameter['id'],$data);
    }

    public function del_product()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id=post('id');
        $this->product_M->up($id,['types'=>0,'iden'=>'']);
    }
}