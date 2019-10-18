<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: 商户页面
 */

namespace app\ctrl\admin;

use app\model\banner as bannerModel;
use app\model\page as pageModel;
use app\validate\BannerPageValidate;

class supplier_page extends BaseController{
	
	public $banner_M;
	public $page_M;
	public $page_S;
	public function __initialize(){
		$this->banner_M = new bannerModel();
		$this->page_M = new pageModel();
		$this->page_S = new \app\service\page();
	}

    //返回数据
    public function index()
    {
        $data=$this->page_S->redis_supplier_page();
        return $data;
    }

    /*提交保存*/
	public function saveedit(){
		(new BannerPageValidate())->goCheck('del_banner');
		$data = post(['slide','navigation','del_banner']);
       
        //固定模块
        $info=$this->page_M->lists_all(['iden'=>'supplier']);
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
                        $this->banner_M->up_byid($vos['id'],$banner);
                    }else{
                        $banner=array();
                        $banner['title']=$vos['title'];
                        $banner['piclink']=$vos['piclink'];
                        $banner['links']=$vos['links'];
                        $banner['aid']=$vo['id'];
                        $this->banner_M->save($banner);
                    }
                    }
                }
            }
            }
            }
        }
        //删除del_banner广告
        if(isset($data['del_banner'])){
        $del_banner = explode('@',$data['del_banner']);
		$abnner_where['id']=$del_banner;
        $this->banner_M->del_all($abnner_where);
        }
        $this->page_S->renew_supplier_page();
		admin_log('修改页面','supplier');  
		return "修改成功";
    }

    public function add_supplier()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id=post('id');
        (new \app\model\user_attach())->up($id,['shop_is_home'=>1]);
    }

    public function del_supplier()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id=post('id');
        (new \app\model\user_attach())->up($id,['shop_is_home'=>0]);
    }
}