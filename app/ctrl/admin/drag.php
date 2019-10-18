<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-18 15:52:42
 * Desc: 引流数据 展业列表
 */
namespace app\ctrl\mobile;
use app\model\drag as drag_Model;
use app\model\drag_num as drag_num_Model;

use app\validate\DragValidate;
use app\validate\IDMustBeRequire;
class drag extends BaseController{

    public $drag_num_M;
    public $drag_M;

	public function __initialize(){
		$this->drag_num_M = new drag_num_Model();
        $this->drag_M = new drag_Model();
	}

  
    public function come()
    {
        $user = $GLOBALS['user'];
        $uid = $GLOBALS['user']['id'];
        $pid = post('pid');
        $sid = post('sid');
        $tid = post('tid');
        $is_ask = post('is_ask',0);
        $is_im = post('is_im',0);
        $is_msg = post('is_msg',0);
        $is_store = post('is_store',0);

        $this->user_change_hit($uid); //总点击量

        $where['pid'] = $pid;
        $where['uid'] = $uid;
        $is_have = $this->drag_M->is_have($where);
        if($is_have){
            $ar = $this->drag_M->have($where);
            $tid_ar = explode(',',$ar['tid']);
            if(!in_array($tid, $tid_ar)){
               array_push($tid_ar,$tid);                    
               $new_tid = implode(',',$tid_ar);
               $data['tid'] = $new_tid;               
            }
            $data['hit[+]'] = 1;
            if($ar['is_ask']!=1){
                 $data['is_ask'] = $is_ask;
            }
            if($ar['is_im']!=1){
                $data['is_im'] = $is_im;
            }
            if($ar['is_msg']!=1){
                $data['is_msg'] = $is_msg;
            }
            if($ar['is_store']!=1){
                $data['is_store'] = $is_store;
            }
            if($ar['push_num_day'] != date('Y-m-d')){
                $data['push_num_day'] = date('Y-m-d');
                $this->user_change_push($pid,$uid,$sid,$tid);
            }

            $data['last_time'] = time();
            $data['view_begin_time'] = time();
            $res = $this->drag_M->up($ar['id'],$data);

        }else{

            $data['uid'] = $uid; //浏览者
            $data['pid'] = post('pid');
            $data['sid'] = post('sid');
            $data['tid'] = post('tid');
            $data['hit'] = 1;
            $data['last_time'] = time();
            $data['is_ask'] = $is_ask;
            $data['is_im'] =$is_im;
            $data['is_msg'] = $is_msg;
            $data['is_store'] = $is_store;
            $data['push_num_day'] = date('Y-m-d');
            $res = $this->drag_M->save($data);
            $this->user_change_push($pid,$uid,$sid,$tid);
        }
        empty($res) && error('更新失败',400);
        return $res;
    }


    //结束时请求
    public function come_over(){
        (new DragValidate())->goCheck('scene_over');
        $uid = $GLOBALS['user']['id'];
        $res = true;
        $where['pid'] = post('pid');
        $where['uid'] = $uid;
        $ar = $this->drag_M->have($where);
        if($ar && $ar['view_begin_time']>0){
            $view_time = time()-$ar['view_begin_time'];
            $data['view_time'] = $view_time;
            $res = $this->drag_M->up($ar['id'],$data);
        }
        empty($res) && error('更新失败',400);
        return $res;
    }

    //互推指数 一人一天一个商品只统计一次
    public function user_change_push($sid){
        $user_M = new \app\model\user();
        $data['push_num[+]'] = 1;   
        $res = $user_M->up($sid,$data);
        $flag = true;
        empty($res) && $flag=false;
        return $flag;
    }

    //更新总点击量
    public function user_change_hit($uid){
        $user_M = new \app\model\user();
        $data['hit_all[+]'] = 1; 
        $res = $user_M->up($uid,$data);
        $flag = true;
        empty($res) && $flag=false;
        return $flag;
    }


    //更新总咨询量
    public function user_change_ask(){
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $user_M = new \app\model\user();
        $data['ask_all[+]'] = 1; 
        $res = $user_M->up($id,$data);
        $flag = true;
        empty($res) && $flag=false;
        return $flag;
    }



    //展业列表 id=商品ID
    public function promote_business(){
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $product_M = new \app\model\product();
        $ar = $product_M->find($id);
        $pro_ar = [];
        $pro_ar['piclink'] = $ar['piclink'];
        $pro_ar['title'] = $ar['title'];
        $pro_ar['created_time'] = $ar['created_time'];
        

        $drag_M = new \app\model\drag();
        $where['pid']= $id;
       

        $page=post("page",1);
        $page_size = post("page_size",10);

        $rs = $drag_M ->lists($page,$page_size,$where);

        $new_ar = [];
        foreach($rs as $one){
            $new_ar[$one['uid']]['hit_num'] = $one['hit'];
            $new_ar[$one['uid']]['view_time_num'] = $one['view_time'];

            $nickname = user_info($one['uid'],'nickname');
            $username = user_info($one['uid'],'username');
            $nickname = $nickname ? $nickname : $username;

            $new_ar[$one['uid']]['nickname'] =$nickname ;
            $new_ar[$one['uid']]['avatar'] = user_info($one['uid'],'avatar');
        }

        foreach($new_ar as $key=>$one){          
            $one['uid'] = $key;
            $data[] = $one;
        }

        $pro_ar['hit'] = count($new_ar);        
        $res['pro_ar'] = $pro_ar;
        $res['new_ar'] = $data;
        return $res;
    }


    //商户 点 我的客户 显示全部客户
    public function my_custom(){      


        $drag_follow_M = new \app\model\drag_follow();

        $sid = $GLOBALS['user']['uid'];
        $custom_type = post('custom_type');
        $where['custom_type'] = $custom_type;
        $where['sid'] = $sid;

        $page=post("page",1);
        $page_size = post("page_size",10);

        $ar = $drag_follow_M->lists($page,$page_size,$where);


        return $ar;












        // $ar = $this->drag_M->lists_all($where);

        // $new_ar = [];
        // foreach($ar as $one){
        //     $new_ar[$one['uid']][] = $one; //相同商户的组
        // }

        // $data = [];
        // foreach($new_ar as $key=>$one_ar){
        //     $data[] = $one_ar;
        // }

        // $page=post("page",1);
        // $page_size = post("page_size",10);
        // $begin = ($page-1)*$page_size;
        // $end = $page*$page_size;

        // $custom_ar = [];
        // foreach($data as $key=>$my_ar){
        //     if($key<$begin){
        //         continue;
        //     }

        //     if($key>=$end){
        //         break;
        //     }
            
        //     foreach($my_ar as $vo){
        //         $custom_ar[$vo['uid']]['hit'] += $vo['hit'];

        //         $custom_ar[$vo['uid']]['hit'] += $vo['view_time'];

        //         if(isset($custom_ar[$vo['uid']]['last_time'])){
        //             if($vo['last_time'] > $custom_ar[$vo['uid']]['last_time']){
        //                 $custom_ar[$vo['uid']]['last_time'] = $vo['last_time'];
        //             }
        //         }else{
        //                 $custom_ar[$vo['uid']]['last_time'] = $vo['last_time'];
        //         }
        //     }
        // }

        // foreach($custom_ar as $key=>$vo){
        //     $nickname = '';
        //     $username = '';
        //     $user = user_info($key);
        //     $nickname = $user('nickname');
        //     $username = $user('username');
        //     $nickname = $nickname ? $nickname : $username;
        //     $custom_ar[$key]['avatar'] = $user['avatar'];
        //     $custom_ar[$key]['nickname'] = $nickname;    
        // }

        // return $custom_ar;
    }


    //单个客户  从我的客户 点进去的页面 再选单个的客户 id 客户ID
    public function custom_info(){
        (new IDMustBeRequire())->goCheck();
        (new DragValidate())->goCheck('scene_custom');
        $id = post('id');
        $sid = post('sid');

        $user = user_info($id);
        $nickname = $user('nickname');
        $username = $user('username');
        $nickname = $nickname ? $nickname : $username;
        $custom_ar['avatar'] = $user['avatar'];
        $custom_ar['nickname'] = $nickname;

        $drag_follow_M = new \app\model\drag_follow();
        $where['id'] = $id;
        $where['sid'] = $sid;
        $ar = $drag_follow_M->have($where);
        if(isset($ar['tel']) && $ar['tel']!=''){
            $tel = $ar['tel'];
        }else{
            $tel = $user['tel'];
        }
        $custom_ar['tel'] = $tel;
        $custom_ar['last_time'] = $ar['follow_time'];

        $where2['sid'] = $sid;
        $where2['uid'] = $uid;
        $where2['pid[!]'] = 0;
        $pro_ar = $this->drag_M->lists_all($where2);

        $product_M = new \app\model\product();
        $product_ar = [];
        foreach($pro_ar as $key=>$one){

            $pro_one = $product_M->find($one);
            $product_ar[$key]['piclink'] = $pro_one['piclink'];
            $product_ar[$key]['title'] = $pro_one['title'];
            $product_ar[$key]['price'] = $pro_one['price'];
            $product_ar[$key]['pay'] = $pro_one['invent_sale'] + $pro_one['real_sale']; // 多少人付款         
        }
        $res['custom_ar'] = $custom_ar;
        $res['product_ar'] = $product_ar;
        return $res;
    }


    









}

 