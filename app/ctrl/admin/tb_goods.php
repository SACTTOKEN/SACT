<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-28 11:23:41
 * Desc: 淘宝客商品券库
 */

namespace app\ctrl\admin;

use app\model\tb_goods as TbGoodsModel;

use app\validate\TbGoodsValidate;
use app\validate\DelValidate;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;

use app\validate\AllsearchValidate;

class tb_goods extends BaseController{
	
	public $tb_goods_M;
	public function __initialize(){
		$this->tb_goods_M  = new TbGoodsModel();	
	}


	/*淘宝商品列表*/
	public function lists()
	{
		(new AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];	

		$title = post('title');
		$coupon_start_time = post('coupon_start_time');
		$coupon_end_time = post('coupon_end_time');
        $price_max = post('price_max');
        $price_min = post('price_min');

		if($title){
			$where['title[~]'] = $title;
		}
	
		if(is_numeric($coupon_start_time)){
			$coupon_end_time = $coupon_end_time ? $coupon_end_time : time();
			$coupon_end_time = $coupon_end_time + 3600*24;
        	$where['coupon_start_time[>=]'] = $coupon_start_time; 	
        	$where['coupon_end_time[<=]'] = $coupon_end_time; 	
        }

        if($price_max && $price_min){
            $where['price[<>]'] = [$price_min,$price_max];
        }

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->tb_goods_M->lists($page,$page_size,$where);

		$count = $this->tb_goods_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}



    /*按id删除*/
    public function del(){
        (new DelValidate())->goCheck();
        $id_str = post('id_str');  
        $id_ar = explode('@',$id_str);
        $res = $this->tb_goods_M->del($id_ar);
        empty($res) && error('删除失败',400);
        return $res;
    }


    /*清空分类商品*/
    public function del_by_fid(){
        $cate_id = post('f_id');
        $where['cate_id'] = $cate_id;
        $res = $this->tb_goods_M->del_all($where);
        empty($res) && error('删除失败',400);
        return $res;
    }


    /*同步好券商品,采集好券清单*/
    public function renew_hjqd(){
    //step.1 采集
    (new \app\validate\PageValidate())->goCheck();
    $page = post('page',1); 
    //$tb = cc('account','taobao');  
    $appkey = c('tbk_appkey');
    $secret_key = c('tbk_secret');
    $adzoneid   = c('tbk_adzoneid');

    require_once(IMOOC."/extend/taobao/TopSdk.php");
    $c = new \TopClient;
    $c->appkey =  $appkey;
    $c->secretKey = $secret_key;
    $req = new \TbkDgItemCouponGetRequest;
    $req->setAdzoneId($adzoneid);
    $req->setPlatform("1");
    //$req->setCat("16,18");
    $req->setPageSize("100");
    //$req->setQ("女装");
    $req->setPageNo($page);
    $resp = $c->execute($req);
    $postObj = simplexml_load_string($resp, 'SimpleXMLElement', LIBXML_NOCDATA);
    $array = json_decode(json_encode($resp),true);
    $ar1 = object_to_array($array);
    $ar1=$ar1['results']['tbk_coupon'];

    //step.2 写入数据库，已存在更新，不存在添加
    $tb_goods_M = new \app\model\tb_goods();
    $data = [];
    if($ar1){
        foreach($ar1 as $one){
            $data = [
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
            $where['num_iid'] = $data['num_iid'];
            $is_have = $tb_goods_M->is_have($where);
            if($is_have){
                $res = $tb_goods_M ->up_all($where,$data);          
            }else{
                if(!empty($data['coupon_link'])){
                    $res = $tb_goods_M ->save($data); 
                }     
            }   
        }
        empty($res) && error('采集失败',400);
    }
    return true;    
    }



    /*同步 选品库商品*/
    public function renew_xpk(){
    (new \app\validate\PageValidate())->goCheck();
    $page = post('page',1);    
    $f_id = post('f_id');   //$f_id = "19472035";
    empty($f_id) && error('类别ID必须',400); 
    //$tb = cc('account','taobao');
    $appkey = c('tbk_appkey');
    $secret_key = c('tbk_secret');
    $adzoneid   = c('tbk_adzoneid');
    require_once(IMOOC."/extend/taobao/TopSdk.php");
    $c = new \TopClient;
    $c->appkey = $appkey;
    $c->secretKey = $secret_key;
    $req = new \TbkUatmFavoritesItemGetRequest;
    $req->setAdzoneId($adzoneid);
    $req->setPlatform("1");
    $req->setPageSize("10");
    //$req->setUnid("3456");//自定义区分不同渠道
    $req->setFavoritesId($f_id);
    $req->setPageNo($page);
    $req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick,shop_title,zk_final_price_wap,event_start_time,event_end_time,tk_rate,status,type,coupon_total_count,coupon_remain_count,coupon_info,coupon_start_time,coupon_end_time,coupon_click_url");
    $resp = $c->execute($req);
    $postObj = simplexml_load_string($resp, 'SimpleXMLElement', LIBXML_NOCDATA);
    $array = json_decode(json_encode($resp),true);
    $ar= object_to_array($array);
   
    if(isset($ar['code']) && $ar['code']==15){ //推广位错误会返回code：15
        return false;
        eixt();
    }

    $ar=$ar['results']['uatm_tbk_item'];
    if($ar){
        foreach($ar as $one){
            $small_images_ar = $one['small_images']['string']; //小图数组
            $data = [
                'piclink'          => $one['pict_url'],
                'title'            => $one['title'],
                'nick'             => $one['nick'],
                'price'            => $one['zk_final_price'],
                'month_sale'       => $one['volume'], //30天销量
                //'commission'       => $one['commission_rate'],//该接口无 佣金比率(%)
                'income_percent'   => $one['tk_rate'],  //收入比率 该商品收入占总收入的百分比
                'seller'           => $one['seller_id'],
                'seller_shop'      => $one['shop_title'],
                'seller_platform'  => $one['user_type'] ? '商城' : '集市', //0表示集市，1表示商城
                'coupon_all'       => isset($one['coupon_total_count']) ? $one['coupon_total_count'] : '0',
                'coupon_left'      => isset($one['coupon_remain_count']) ? $one['coupon_remain_count'] : '0',
                'coupon_info'      => isset($one['coupon_info']) ? $one['coupon_info'] : '0',
            'coupon_start_time'=> isset($one['coupon_start_time']) ? strtotime($one['coupon_start_time']) : '0',
                'coupon_end_time'  => isset($one['coupon_end_time']) ? strtotime($one['coupon_end_time']) : '0',
                'tbk_link'         => $one['item_url'],//商品链接
                'coupon_link'      => isset($one['coupon_click_url']) ? $one['coupon_click_url'] : '0',//商品优惠券推广链接
                'share_coupon_link'=> isset($one['coupon_click_url']) ? $one['coupon_click_url'] : '0',
                'num_iid'          => $one['num_iid'],
                'cate_id'          => $f_id,//好券清单
                ];
            $where['num_iid'] = $data['num_iid'];
            $is_have = $this->tb_goods_M->is_have($where);
            if($is_have){
                    $res = $this->tb_goods_M ->up_all($where,$data);          
            }else{
                if(!empty($data['coupon_link'])){
                    $res = $this->tb_goods_M ->save($data);      
                }               
            }   
        }        
        empty($res) && error('采集失败',400);
        }
        return true;
    }

}