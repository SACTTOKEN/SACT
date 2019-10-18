<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-30 16:34:36
 * Desc: 淘宝客前台
 */
namespace app\ctrl\mobile;

use app\validate\IDMustBeRequire;
use app\validate\TbGoodsValidate;
use app\model\tb_goods as TbGoodsModel;
use app\model\tb_cate as TbCateModel;


class plugin_tbk extends BaseController
{
	
    public $tb_goods_M;
    public $tb_cate_M;
    public function __initialize(){
        $this->tb_goods_M = new TbGoodsModel();
        $this->tb_cate_M = new TbCateModel();
    }


    /*前台列表*/
    public function tbk_lists(){
        $cate_id = post('cate_id',1); //tb_cate表的f_id
        if($cate_id){
            $where['cate_id'] = $cate_id;
        }
        $keyward = post('keyward','');
        if($keyward){
            $where['title[~]']=$keyward;
        }

        $page=post("page",1);
        $page_size = post("page_size",10);      
        $data=$this->tb_goods_M->lists($page,$page_size,$where);
        //cs($this->tb_goods_M->log(),1);
       
        $res['data'] = $data; 
        return $res; 
    }

    /*前台商品详情*/
    public function tbk_one(){
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $res = $this->tb_goods_M->find($id);
        return $res;
    }

    /*前台商品类别*/
    public function tbk_cate(){

        $where['ORDER'] = ['sort'=>'DESC'];
        $res = $this->tb_cate_M->lists_all($where);
        return $res;
    }

    public function tbk_kl(){
        (new TbGoodsValidate())->goCheck('tbk_kl');
        $url=post("url");
        $pic=post("pic");
        $title=post("title");
       
        $tb = cc('account','taobao');        
        require_once(IMOOC."/extend/taobao/TopSdk.php");
        $c = new \TopClient;
        $c->appkey =  $tb['appkey'];
        $c->secretKey = $tb['secretKey'];
        $req = new \TbkTpwdCreateRequest;
        $req->setText($title);
        $req->setUrl($url);
        $req->setLogo($pic);
        $req->setExt("{}");
        $resp = $c->execute($req);
        $array = json_decode(json_encode($resp),true);
        $ar1 = object_to_array($array);
        return $ar1['data']['model'];
    }

    /*前台搜索 直接采集显示*/
    public function tbk_search(){
        (new TbGoodsValidate())->goCheck('scene_vueSearch');
        $keyward = post('keyward','');
        (new \app\validate\PageValidate())->goCheck();
        $page = post('page',1); 
        $page_size = post("page_size",10); 

        $tb = cc('account','taobao');        
        require_once(IMOOC."/extend/taobao/TopSdk.php");
        $c = new \TopClient;
        $c->appkey =  $tb['appkey'];
        $c->secretKey = $tb['secretKey'];
        $req = new \TbkDgItemCouponGetRequest;
        $req->setAdzoneId($tb['AdzoneId']);
        $req->setPlatform("1");
        //$req->setCat("16,18");
        $req->setPageSize($page_size);
        $req->setQ($keyward);
        $req->setPageNo($page);
        $resp = $c->execute($req);
        $postObj = simplexml_load_string($resp, 'SimpleXMLElement', LIBXML_NOCDATA);
        $array = json_decode(json_encode($resp),true);
        $ar1 = object_to_array($array);
        $ar1=$ar1['results']['tbk_coupon'];

        $new_ar = [];
        if($ar1){
        foreach($ar1 as $key=>$one){
            $new_ar[$key] = [
                'small_images'     => $one['small_images'],
                'piclink'          => $one['pict_url'],
                'title'            => $one['title'],
                'nick'             => $one['nick'],
                'price'            => $one['zk_final_price'],
                'month_sale'       => $one['volume'], //30天销量
                'commission'       => $one['commission_rate'],//佣金比率(%)
                'seller'           => $one['seller_id'],
                'seller_shop'      => $one['shop_title'],
                'seller_platform'  => $one['user_type'] ? '商城' : '集市', //0表示集市，1表示商城
                'coupon_all'       => $one['coupon_total_count'],
                'coupon_left'      => $one['coupon_remain_count'],
                'coupon_info'      => $one['coupon_info'],
                'coupon_start_time'=> strtotime($one['coupon_start_time']),
                'coupon_end_time'  => strtotime($one['coupon_end_time']),
                'tbk_link'         => $one['item_url'],//商品链接
                'coupon_link'      => $one['coupon_click_url'],//商品优惠券推广链接
                'share_coupon_link'=> $one['coupon_click_url'],
                'num_iid'          => $one['num_iid'],
                'cate_id'          => '1',//好券清单
                ];
            }
          
            $res['data'] = $new_ar; 
            return $res;    
        }else{
            return false;
        }
    }

}