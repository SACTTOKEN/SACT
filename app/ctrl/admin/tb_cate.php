<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-28 11:23:41
 * Desc: 淘宝客分类
 */

namespace app\ctrl\admin;

use app\model\tb_cate as TbCateModel;

use app\validate\TbCateValidate;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;

use app\validate\AllsearchValidate;

class tb_cate extends BaseController{
	
	public $tb_cate_M;

	public function __initialize(){
		$this->tb_cate_M  = new TbCateModel();	
	}


    /*更新  选品库类别*/
    public function renew_xpk_cate(){
        $appkey = c('tbk_appkey');
        $secret_key = c('tbk_secret');      
        require_once(IMOOC."/extend/taobao/TopSdk.php");
        $c = new \TopClient;
        $c->appkey = $appkey;
        $c->secretKey = $secret_key;
        $req = new \TbkUatmFavoritesGetRequest;
        $req->setPageNo("1");
        $req->setPageSize("20");
        $req->setFields("favorites_title,favorites_id,type");
        $req->setType("1");
        $resp = $c->execute($req);
        $array = json_decode(json_encode($resp),true);
        $ar= object_to_array($array);
        $arr=$ar['results']['tbk_favorites'];
        //cs($arr);  //Array ( [favorites_id] => 19580947 [favorites_title] => 数码 [type] => 1 ) 

    
        $where = [];
        $data = [];
        if (count($arr) == count($arr,1)) { //echo '是一维数组';
            $where['f_id'] = $arr['favorites_id'];
            $data['f_type'] = $arr['type'];
            $data['f_title'] = $arr['favorites_title'];

            $is_have = $this->tb_cate_M->is_have($where);
            if($is_have){
                $res = $this->tb_cate_M->up_all($where,$data);
            }else{
                $data['f_id'] = $arr['favorites_id'];
                $res = $this->tb_cate_M->save($data);
            } 

        } else {  //echo '不是一维数组';
            
            foreach($arr as $one){
            $where = [];
            $data = [];

            $where['f_id'] = $one['favorites_id'];

            $data['f_type'] = $one['type'];
            $data['f_title'] = $one['favorites_title'];

            $is_have = $this->tb_cate_M->is_have($where);

            if($is_have){
                $res = $this->tb_cate_M->up_all($where,$data);
            }else{
                $data['f_id'] = $one['favorites_id'];
                $res = $this->tb_cate_M->save($data);
            } 
            }

        }

        empty($res) && error('更新失败',400);
        return true;
    }


    /*分类列表*/
    public function lists()
    {
        $where = [];
        $page=post("page",1);
        $page_size = post("page_size",100);
        
        $where['f_title[!]']= '好券清单';
        $data=$this->tb_cate_M->lists($page,$page_size,$where);

        $first_ar = $this->tb_cate_M->find(1);   //好券清单ID始终为1

        if($first_ar){
            array_unshift($data,$first_ar);    
        }     

        $count = $this->tb_cate_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }


    /*按id删除类别*/
    public function del(){
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        if($id==1){error('优品清单类不能删除');}
        $f_id = $this->tb_cate_M->find($id,'f_id');
        empty($f_id) && error('分类不存在',400);
        $tb_goods_M = new \app\model\tb_goods();
        $where['cate_id'] = $f_id;
        $is_have = $tb_goods_M->is_have($where);
        !empty($is_have) && error('请先清空该分类商品',400); 
        $res = $this->tb_cate_M->del($id);
        empty($res) && error('删除失败',400);
        return $res;
    }


    /*输入数字排序*/
    public function sort_by_number(){
        $sort = post('sort');
        $id = post('id');   
        $data['sort'] = $sort;
        $res = $this->tb_cate_M->up($id,$data);
        empty($res) && error('排序失败',400);       
        admin_log('优品库类目排序',$id);    
        return $res;
    }

    


}