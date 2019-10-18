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
use app\model\drag_follow as drag_follow_Model;

use app\validate\DragValidate;
use app\validate\IDMustBeRequire;
use core\lib\model as Model;
class drag extends BaseController{

    public $drag_num_M;
    public $drag_M;
    public $model;

    public function __initialize(){
        $zyfh = plugin_is_open('zyfh');
        if(!$zyfh){return false;}
        $this->drag_num_M = new drag_num_Model();
        $this->drag_M = new drag_Model();
        $this->drag_follow_M = new drag_follow_Model();
        $this->model = new Model;
    }

    public function come()
    {
        $user = $GLOBALS['user'];
        $uid = $GLOBALS['user']['id'];
        $pid = post('pid',0);
        $sid = post('sid',0);
        $tid = post('tid',0);
        $product_M = new \app\model\product();
        $user_M = new \app\model\user();

        if($pid>0){
            $flag_0 = $product_M->is_have(['id'=>$pid]);
            if(!$flag_0){
                return "商品不存在";
            }
        }

        if($uid>0){
            $flag_1 = $user_M->is_have(['id'=>$uid]);
            if(!$flag_1){
                return "用户不存在"; 
            }
        }

        if($sid>0){
            $flag_2 = $user_M->is_have(['id'=>$sid]);
            if(!$flag_2){
                return "用户不存在"; 
            }
        }

        if($tid>0){
            $flag_3 = $user_M->is_have(['id'=>$tid]);
            if(!$flag_3){
                return "用户不存在"; 
            }
        }
        
        // if(empty($tid)){
        //     return  "无推广人的不记录"; //无推广人的不记录
        // }
        if($sid == $uid){
            return "商户浏览自己的商品不记录";  //商户浏览自己的商品不记录
        }
        if($uid == $tid){
            return "推广人浏览自已推广的不记录";  //推广人浏览自已推广的不记录
        }

        // $user_M = new \app\model\user();
        // $is_have_tid = $user_M->is_have(['id'=>$tid]);
        // if(!$is_have_tid){return '转发者用户不存在';}

        $user_nickname = user_info($uid,'nickname');
        $user_avatar = user_info($uid,'avatar');

        if(empty($user_nickname) || empty($user_avatar)){
            return "无昵称和头像的不记录"; //无昵称和头像的不记录
        }

        $is_ask = post('is_ask',0);
        $is_im = post('is_im',0);
        $is_msg = post('is_msg',0);
        $is_store = post('is_store',0);

        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  
        $model->action();
        $redis->multi();

        $where['pid'] = $pid;
        $where['uid'] = $uid;
        $is_have = $this->drag_M->is_have($where);

        if($is_have){
            $ar = $this->drag_M->have($where);

            if($tid!=0 && $tid != $uid){
                if($ar['tid']==0){
                    $data['tid'] = $tid;  
                }else{
                    $tid_ar = explode(',',$ar['tid']);
                    if(!in_array($tid, $tid_ar)){
                       array_push($tid_ar,$tid);                    
                       $new_tid = implode(',',$tid_ar);
                       $data['tid'] = $new_tid;               
                    }
                }
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

            if($is_store==1  && $ar['is_store']!=1){  //进店
                $data['is_store'] = $is_store;
            }

            if($tid>0){
                $this->user_change_push($tid,$pid,$uid);
            }

            $data['last_time'] = time();
            $data['view_begin_time'] = time();
            $data['view_time[+]'] = 10; //10秒轮循
            $res = $this->drag_M->up($ar['id'],$data);
            if(empty($res)){return "更新失败1";}

        }else{

            $data['uid'] = $uid; //浏览者
            $data['pid'] = $pid;
            $data['sid'] = $sid;
            $data['tid'] = $tid;
            $data['hit'] = 1;
            $data['last_time'] = time();
            $data['is_ask'] = $is_ask;
            $data['is_im'] =$is_im;
            $data['is_msg'] = $is_msg;
            if($is_store==1){
                $data['is_store'] = $is_store;
            }    
            $data['push_num_day'] = date('Y-m-d');
            $data['view_begin_time'] = time();
            $data['view_time'] = 10; //10秒轮循
            $res = $this->drag_M->save($data);
            if(empty($res)){return '更新失败2';} 
            $res =$this->user_change_push($tid,$pid,$uid);
            //if(empty($res)){return '更新失败3';}
        }
        $this->user_change_hit($uid); //总点击量

        //商户每日统计
        $stage = date('Ymd'); 
        $where2['sid'] = $sid;
        $where2['stage'] = $stage;
        $is_have_num = $this->drag_num_M->have($where2);
        if($is_have_num){
            if($is_ask==1){
                $data2['ask_num[+]'] = 1;
            }
            if($is_msg==1){
                $data2['msg_num[+]'] = 1;
            }
            if($is_im==1){
                $data2['im_num[+]'] = 1;
            }
            $data2['hit_num[+]'] =1;

            $res = $this->drag_num_M->up($is_have_num['id'],$data2);
            if(empty($res)){return '商户每日统计失败2';}

            //empty($res) && error('商户每日统计失败2',400);          
        }else{
            if($is_ask==1){
                $data2['ask_num'] = 1;
            }
            if($is_msg==1){
                $data2['msg_num'] = 1;
            }
            if($is_im==1){
                $data2['im_num'] = 1;
            }
            $data2['hit_num'] =1;  
            $data2['sid'] = $sid;
            $data2['stage'] = date('Ymd');
            $res2 = $this->drag_num_M->save($data2);
             if(empty($res2)){return '商户每日统计失败1';}
            //empty($res2) && error('商户每日统计失败1',400);
        }


        //引流客户 drag_follow
        if($sid!=0){
            $where3['uid']  = $uid;
            $where3['sid']  = $sid;
            $is_have_follow = $this->drag_follow_M->have($where3);
            if($is_have_follow){
                $data3['hit_all[+]']     = 1;
                $data3['view_last_time'] = time();
                $data3['view_time_all[+]'] = 10; //10秒轮循
                $res3 = $this->drag_follow_M->up($is_have_follow['id'],$data3);
                if(empty($res3)){return true;}
                //empty($res3) && error('加载客户失败2',400);
            }else{
                $data3['hit_all']        = 1;
                $data3['view_last_time'] = time();
                $data3['custom_type']    = 0; //不给客户类型
                $data3['uid'] = $uid;
                $data3['sid'] = $sid;
                $data3['view_time_all'] = 10; //10秒轮循
                $res3 = $this->drag_follow_M->save($data3);
                if(empty($res3)){return true;}
            }
        }

        $model->run();
        $redis->exec();

        return $res;
    }


    //结束时请求
    public function come_over(){
        (new DragValidate())->goCheck('scene_over');
        $uid = $GLOBALS['user']['id'];
        $res = true;
        $where['pid'] = post('pid',0);
        $where['sid'] = post('sid',0);
        $where['uid'] = $uid;
        $ar = $this->drag_M->have($where);
        $view_time=0;
        if($ar && $ar['view_begin_time']>0){
            //$view_time = time()-$ar['view_begin_time'];
            $data['view_time[+]'] = 10;
            $data['view_end_time'] = time();
            $res = $this->drag_M->up($ar['id'],$data);
        }
        //empty($res) && error('更新失败',400);

        $where3['uid']  = $uid;
        $where3['sid']  = post('sid',0);
        $is_have_follow = $this->drag_follow_M->have($where3);
        if($is_have_follow){          
            $data3['view_time_all[+]'] = 10;
            $data3['view_last_time'] = time();
            $res3 = $this->drag_follow_M->up($is_have_follow['id'],$data3);
        //empty($res3) && error('加载客户失败3',400);
        }

        return $res;
    }

    //互推指数 一人一天一个商品只统计一次 我转发别人的商品,发布者不等于推广者
    public function user_change_push($tid,$pid,$uid){
        $where['uid'] = $uid;
        $where['pid'] = $pid;
        $ar = $this->drag_M->have($where);
        if( date('Ymd')!=$ar['push_num_day']  && $ar['sid']!=$tid){   
            $up['push_num_day'] =  date('Ymd');//"pid=".$pid."@uid=".$uid."@tid=".$tid;
            $this->drag_M->up($ar['id'],$up);
            $user_M = new \app\model\user();
            $data['push_num[+]'] = 1;   
            $res = $user_M->up($tid,$data);
        }    
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

        $data = [];
        foreach($new_ar as $key=>$one){          
            $one['uid'] = $key;
            $data[] = $one;
        }

        $pro_ar['hit'] = count($new_ar);        
        $res['pro_ar'] = $pro_ar;
        $res['new_ar'] = $data;
        return $data;
    }


   
    //展业列表头部 id=商品ID
    public function promote_business_head(){
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
        $pro_ar['hit'] =  $drag_M ->new_count($where);                
        return $pro_ar;
    }



    //聚力兽 时间排行 -》 人-》是否转发 -》 分享者是A 且 发布者是B的 所有浏览者信息。
    public function change_moon(){
        (new IDMustBeRequire())->goCheck();
        $fabu_id = $GLOBALS['user']['id'];
        $feixiang_id = post('id');
        $sid = $fabu_id;
        $tid = $feixiang_id;

        $page=post("page",1);
        $page_size = post("page_size",10);
        $startRecord=($page-1)*$page_size;  

        $sql = "select * from drag where CONCAT(',',tid,',') like '%,".$tid.",%' and sid=".$sid." order by last_time desc,id desc limit ".$startRecord.",".$page_size;

            $rating = user_info($sid,'rating');
            if($rating == '1'){
            //$where['LIMIT'] = 3; //普通会员只显示三条
                $sql2  = "select * from drag where CONCAT(',',tid,',') like '%,".$tid.",%' and sid=".$sid." order by last_time desc,id desc limit 0,3";
                $ar = $this->model::$medoo->query($sql2)->fetchAll(); 
            }else{  
                $ar = $this->model::$medoo->query($sql)->fetchAll(); 
            }
            unset($where['LIMIT']);
            $sql3 = "select * from drag where CONCAT(',',tid,',') like '%,".$tid.",%' and sid=".$sid." order by last_time desc,id desc";
            $ar_all = $this->model::$medoo->query($sql3)->fetchAll(); 
            $count = count($ar_all);

        $product_M = new \app\model\product();
        foreach($ar as $key=>$one){
            $user = user_info($one['uid']);
            $username = $user['username'];
            $nickname = $user['nickname'];
            $nickname = $nickname ? $nickname : $username;          
            $data[$key]['nickname']  = $nickname;
            $data[$key]['avatar']  =  $user['avatar'];
            $data[$key]['rating_cn'] = $user['rating_cn'];
            $data[$key]['uid']  = $one['uid'];

            if($one['pid']>0){
                $data[$key]['pro_title'] = $product_M->find($one['pid'],'title');
            }else{
                $data[$key]['pro_title'] = '';
            }  

            $data[$key]['view_time'] = $one['view_time'];
            $data[$key]['hit'] = $one['hit'];
        }

        $res['count'] = $count;
        $res['ar'] = $data;
        return $res;    
    }
    





    //单个客户   从我的客户 点进去的页面 再选单个的客户 id 客户ID
    //从排行列表 点进去也是这个页面
    public function custom_info(){
        (new IDMustBeRequire())->goCheck();
        $uid = post('id');
        $sid = $GLOBALS['user']['id'];
        $user = user_info($uid); //客户页面
        $nickname = $user['nickname'];
        $username = $user['username'];
        $nickname = $nickname ? $nickname : $username;
        $custom_ar['avatar'] = $user['avatar'];
        $custom_ar['nickname'] = $nickname;
        $drag_follow_M = new \app\model\drag_follow();
        $where['uid'] = $uid;
        $where['sid'] = $sid;
        $ar = $drag_follow_M->have($where);
     

        $custom_ar['is_msg'] = '';
        $custom_ar['is_ask'] = '';
        $custom_ar['is_store'] = '';
        $custom_ar['is_tid'] = '';
        $custom_ar['uid_now'] = $uid;

        if(isset($ar['tel']) && $ar['tel']!=''){
            $tel = $ar['tel'];
            $where1['ask_tel[!]'] = '';
            $where1['uid'] = $uid;
            $where1['sid'] = $sid;
            $where1['ORDER'] =['update_time'=>'DESC'];
            $rs1 = $this->drag_M->have($where1);          
            $custom_ar['is_ask']  = $rs1['ask_tel'];
            //$where2['msg_tel[!]'] = '';
            $where2['uid'] = $uid;
            $where2['sid'] = $sid;
            $where2['ORDER'] =['update_time'=>'DESC'];
            $rs2 = $this->drag_M->have($where2);  
            $custom_ar['is_msg']  = $rs2['msg_tel'];
        }else{
            $tel = $user['tel'];
        }


        $custom_ar['tel'] = $tel;
        $custom_ar['last_time'] = $ar['view_last_time'];
        $custom_ar['custom_type'] = $ar['custom_type'];
        $custom_ar['follow_id'] = $ar['id'];



        $where2['sid'] = $sid;
        $where2['uid'] = $uid;
        $where2['pid[!]'] = 0;
        $pro_ar = $this->drag_M->lists_all($where2);




        $sql = "select * from drag where CONCAT(',',tid,',') like '%,".$uid.",%' and sid=".$sid." order by push_num_day desc,id desc"; //是否有转发该商品
        $is_tid_ar = $this->model::$medoo->query($sql)->fetch();
        if($is_tid_ar){
            $custom_ar['is_tid'] = 1;
        }

        $product_M = new \app\model\product();
        $product_ar = [];
        

        if(!empty($pro_ar)){
            foreach($pro_ar as $key=>$one){
                $pro_one = $product_M->find($one['pid']);
                if(empty($pro_one)){
                    continue;
                }

                if($one['is_store']==1){
                    $custom_ar['is_store'] =1;    
                }     

                $product_ar[$key]['piclink'] = $pro_one['piclink'];
                $product_ar[$key]['title'] = $pro_one['title'];

                $product_ar[$key]['hit_num'] = $one['hit'];
                $product_ar[$key]['view_time'] = $one['view_time'];
                //$product_ar[$key]['price'] = $pro_one['price'];
                //$product_ar[$key]['pay'] = $pro_one['invent_sale'] + $pro_one['real_sale']; // 多少人付款 
            }
        }   

        //$custom_ar['is_store'] =1; 
        $res['custom_ar'] = $custom_ar;
        $res['product_ar'] = $product_ar;
        $res['tid_ar'] = $this->tid_list($uid,$sid);
        return $res;
    }


    //资源排行-》展业-》人进点去 针对单个商品这个人的页面
    //个人-》首页微展业 点进去的商品再点进去 展业-》人进点去
    public function product_custom_info(){
        (new IDMustBeRequire())->goCheck();
        $pid = post('pid');
        $uid = post('id');
        $user = user_info($uid); //客户页面
        $nickname = $user['nickname'];
        $username = $user['username'];
        $nickname = $nickname ? $nickname : $username;
        $custom_ar['avatar'] = $user['avatar'];
        $custom_ar['nickname'] = $nickname;

        $where['pid'] = $pid;  //150
        $where['uid'] = $uid;  //81
        $ar = $this->drag_M->have($where);

        $custom_ar['is_msg'] = $ar['msg_tel'];
        $custom_ar['is_ask'] = $ar['ask_tel'];
        $custom_ar['last_time'] = $ar['last_time'];
        $custom_ar['hit'] = $ar['hit'];
        $custom_ar['view_time'] = $ar['view_time'];
        $custom_ar['tel'] = user_info($uid,'tel');
        $custom_ar['is_store'] = $ar['is_store'];


        $sql = "select * from drag where CONCAT(',',tid,',') like '%,".$uid.",%' and pid=".$pid." order by push_num_day desc,id desc"; //是否有转发该商品
        $is_tid_ar = $this->model::$medoo->query($sql)->fetch();
        if($is_tid_ar){
            $custom_ar['is_tid'] = 1;
        }


        $where2['uid'] = $uid;
        $ar2 = $this->drag_M->lists_all($where2);

        $product_M = new \app\model\product();
        $product_ar = [];
        foreach($ar2 as $key=>$one){
                $pro_one = $product_M->find($one['pid']);
                if(empty($pro_one)){
                    continue;
                }
                $product_ar[$key]['pid'] = $pro_one['id'];
                $product_ar[$key]['piclink'] = $pro_one['piclink'];
                $product_ar[$key]['title'] = $pro_one['title'];

                $product_ar[$key]['hit_num'] = $one['hit'];
                $product_ar[$key]['view_time'] = $one['view_time'];
        }

        $res['custom_ar'] = $custom_ar;
        $res['product_ar'] = $product_ar;
        $res['tid_ar'] = $this->tid_list2($uid,$pid);
        $where3['sid'] = $GLOBALS['user']['id'];
        $where3['uid'] = $uid;
        $follow_ar = $this->drag_follow_M->have($where3);
        $follow_id = isset($follow_ar['id']) ? $follow_ar['id'] : 0;

        $res['custom_ar']['follow_id'] = $follow_id;
        $res['custom_ar']['custom_type'] = $follow_ar['custom_type'];
      
        return $res;
    }

    //推荐这个商品给这个用户的 所有人
    public function tid_list2($uid,$pid){
        $where['uid'] = $uid;
        $where['pid'] = $pid;    
        $one = $this->drag_M->have($where);
        if($one['tid']!=0){
            $tid_ar = explode(',',$one['tid']);
        }
    
        if(empty($tid_ar)){return [];}
        $res = [];
        $user_M = new \app\model\user();
        foreach($tid_ar as $key => $one){
            $u_ar = $user_M->find($one);
            $nickname  = $u_ar['nickname'];
            $username  = $u_ar['username'];
            $nickname  = $nickname ? $nickname : $username;
            $avatar = $u_ar['avatar'];    
            $res[$key]['nickname'] = $nickname;
            $res[$key]['avatar'] = $avatar;
            $res[$key]['last_time'] =$u_ar['login_time']; 
            $res[$key]['uid'] = $one;
        }
        return $res;
    }


    //数据看板
    public function databoard(){
        $sid = $GLOBALS['user']['id']; //商户ID
        $drag_num_M = new \app\model\drag_num();

        $where_1['sid'] = $sid;
        $num_1 = $this->drag_num_M->find_sum('hit_num',$where_1); //总点击量

        $where_2['stage'] = date('Ymd');
        $where_2['sid'] = $sid;
        $num_2 = $this->drag_num_M->find_sum('hit_num',$where_2); //今日点击

        $where_3['stage'] = date("Ymd", strtotime('-1 day'));
        $where_3['sid'] = $sid;
        $num_3 = $this->drag_num_M->find_sum('hit_num',$where_3); //昨日点击
   
        $where_4['sid']  = $sid;
        $num_4 = $this->drag_num_M->find_sum('ask_num',$where_4); //总咨询量
       
        $where_5['sid']  = $sid;
        $where_5['stage'] = date('Ymd');
        $num_5 = $this->drag_num_M->find_sum('ask_num',$where_5); //今日咨询
  
        $where_6['sid']    = $sid;
        $where_6['stage']  = date("Ymd", strtotime('-1 day'));
        $num_6 = $this->drag_num_M->find_sum('ask_num',$where_6); //昨日咨询

        $res = [
            'num_1' => $num_1,
            'num_2' => $num_2,
            'num_3' => $num_3,
            'num_4' => $num_4,
            'num_5' => $num_5,
            'num_6' => $num_6,
        ];
        return $res;
    }





    //客户访问记录排序列表（最近三天的）
    public function custom_list(){
        $type = post('type');
        $sid = $GLOBALS['user']['id']; //商户ID
        $where['sid'] = $sid;
        $page=post("page",1);
        $page_size = post("page_size",10);
        $sn = post('sn','hit');
        $product_M = new \app\model\product();
        $day_3 = time()-(3600*24*7);

        $rating = user_info($sid,'rating');
        $created_time = user_info($sid,'created_time');
        // 时间排行：最新浏览的排序  列下来 last_time
        // 时长排行：访问总计时间最长的排序  view_time
        // 次数排行：根据总访问次数最多的排序  hit
        // 资源排行：被点击最多的产品排序   product
        if($sn!='product'){

            $where['ORDER'] = [$sn=>'DESC'];

            if($type!='all'){
            $where['created_time[>]'] = $day_3; //最近七天
            }

            if($rating == '1' && floatval($created_time+(3600*24*3)) <time() ){
            $where['LIMIT'] = 3; //普通会员只显示三条
            $ar = $this->drag_M->lists_all($where);   
            }else{  
            $ar = $this->drag_M->lists_drag($page,$page_size,$where);      
            }

            unset($where['LIMIT']);
            $count = $this->drag_M->new_count($where);


            //cs($this->drag_M->log(),1);
            foreach($ar as &$one){
                $one['avatar'] = user_info($one['uid'],'avatar');
                $nickname = user_info($one['uid'],'nickname');
                $username = user_info($one['uid'],'username');
                $nickname = $nickname ? $nickname : $username;
                if($one['pid']>0){
                    $one['pro_title'] = $product_M->find($one['pid'],'title');
                }else{
                    $one['pro_title'] = '';
                }  
                $one['nickname'] = $nickname;
            }

            $res['count'] = $count;
            $res['ar'] = $ar;
            return $res;

        }else{ 

            $where2['sid'] = $sid;
            if($rating==1){$where2['LIMIT'] = 3;}//普通会员只显示三条
            $ar = $product_M->lists_all($where2);

            $count = $product_M->new_count($where2);
            foreach($ar as &$one){
                $one['hit_all'] = $this->drag_M->find_sum('hit',['pid'=>$one['id']]); //单个商品的点击数
                $one['view_time'] = $this->drag_M->find_sum('view_time',['pid'=>$one['id']]);
                unset($one['content']);
                unset($one['attr']);
                unset($one['sku_json']);
            }
            unset($one);
            $hit_order = array_column($ar, 'hit_all');
            array_multisort($hit_order,SORT_DESC,$ar);
            $res['ar'] = $ar;
            $res['count'] = $count;
            return $res;


        }
    }


    //一周每天商品点击量典线图
    public function hit_week(){
        $sid = $GLOBALS['user']['id'];

        $day = [];
        for($i=0;$i<7;$i++){
            $day[] = date('Ymd',strtotime('-'.$i. 'day'));
        }

        foreach($day as $stage){
            $where['sid'] = $sid;
            $where['stage'] = $stage;
            $num[] = $this->drag_num_M->find_sum('hit_num',$where);
        }

        $res['day'] = $day;
        $res['num'] = $num;
        return $res;
    }


    //推荐人列表 图像,名称,最近浏览时间
    public function tid_list($uid,$sid){
        $where['uid'] = $uid;
        $where['sid'] = $sid;    
        $ar = $this->drag_M->lists_all($where);

        $new_ar = [];
        foreach($ar as $one){
            $new_ar[$one['tid']] = $one;
        }


        if(empty($new_ar)){return [];}
        $new_tid_ar = [];
        $tid_ar = [];


        foreach($new_ar as $one){
            if($one['tid']!=0){
                $tid_ar = explode(',',$one['tid']);
                $new_tid_ar = array_merge($new_tid_ar,$tid_ar);
            }
        }

        if(empty($new_tid_ar)){return [];}
        $res = [];
        $user_M = new \app\model\user();
        foreach($new_tid_ar as $key => $one){
            $u_ar = $user_M->find($one);
            $nickname = $u_ar['nickname'];
            $username  = $u_ar['username'];
            $nickname  = $nickname ? $nickname : $username;
            $avatar = $u_ar['avatar'];    
            $res[$key]['nickname'] = $nickname;
            $res[$key]['avatar'] = $avatar;
            $res[$key]['last_time'] =$u_ar['login_time']; 
            $res[$key]['uid'] = $one;
        }
        return $res;
    }    



    //微展业 我分享给他人的商品
    public function mini_share(){
        $uid = $GLOBALS['user']['id'];

        $where['tid'] = $uid;
        $where['ORDER'] = ['last_time'=>'DESC'];
        $ar = $this->drag_M->lists_all($where);
        $pro_M = new \app\model\product();

        $res = [];
        foreach($ar as $key=>$one){
            if($one['pid']){                
                $res[$one['pid']][] = $one['uid'];
            }
        }

        $data = [];
        foreach($res as $pid=>$uid_ar){
            $view_num = count($uid_ar);
            $F4 = array_slice($uid_ar,0,4);
            $F4_avatar = [];
            foreach($F4 as $one){
                $F4_avatar[] = user_info($one,'avatar');
            }

            $pro_ar = $pro_M->find($pid,['id','piclink','title']);
            $data[] = [
                'pid'     => $pid,
                'title'   => $pro_ar['title'],
                'piclink' => $pro_ar['piclink'],
                'view_num'=> $view_num,
                'view_ar' => $F4_avatar,
            ];  
        }
        return $data;
    }



    //微展页 点商品 进去显示 tid是我的 商品 浏览过的人
    public function show_tid_view(){
        $pid = post('pid');
        $tid = $GLOBALS['user']['id'];
        $where['tid'] = $uid;
        $where['pid'] = $pid;

        $page=post("page",1);
        $page_size = post("page_size",10);
        $ar = $this->drag_M->lists_drag($page,$page_size,$where);

        foreach($ar as $key=>$one){
            $u_ar = user_info($one['uid']);
            $ar[$key]['avatar'] = $u_ar['avatar'];
            $nickname = $u_ar['nickname'];
            $username = $u_ar['username'];
            $nickname = $nickname ? $nickname : $username;
            $ar[$key]['nickname'] = $nickname;
        }

        return $ar;
    }




}

 