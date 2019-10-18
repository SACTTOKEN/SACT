<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-11 13:40:31
 * Desc: 红包服务
 */
namespace app\service;
class coupon{

    public $coupon_M;
    public $packet_M;
    public function __construct()
    {
        $this->coupon_M = new \app\model\coupon(); 
        $this->packet_M = new \app\model\packet();
    }

    //商品页红包列表
    public function product_coupon($pid,$sid)
    {
        //单品红包
        $where1['cdn_pid']=$pid;
        $where1['page_get']=0;
        $where1['OR']=['full_num[>]receive_num'];
        $where1['OR']['full_num']=0;
        $data1=$this->packet_M->lists_all($where1);
        //商家红包
        $where2['cdn_sid']=$sid;
        $where2['cdn_pid']=0;
        $where2['page_get']=0;
        $where2['OR']=['full_num[>]receive_num'];
        $where2['OR']['full_num']=0;
        $data2=$this->packet_M->lists_all($where2);   
        $data = array_merge($data1,$data2);
        $rating=(new \app\model\rating())->lists_all('',['id','title']);
        $rating=array_column($rating, 'title', 'id');
        foreach($data as &$vo){
            $where['uid']=$GLOBALS['user']['id'];
            $where['packet_id']=$vo['id'];
            $where['is_use']=0;
            $where['end_time[>]']=time();
            $ar=$this->coupon_M->is_have($where);
            if($ar){
                $vo['is_receive']=1;
            }else{
                $vo['is_receive']=0;
            }
            if($vo['limit_lv']==0 || !isset($rating[$vo['limit_lv']])){
                $vo['limit_lv_cn']="无使用门槛 最多优惠".sprintf("%.2f",$vo['money']).'元';
            }else{
                $vo['limit_lv_cn']=$rating[$vo['limit_lv']]."等级才能使用 最多优惠".sprintf("%.2f",$vo['money']).'元';
            }
        }
        return $data;
    }

    //领取红包
    public function receive($id,$page_get,$source='')
    {
        $where['id']=$id;
        $where['page_get']=$page_get;
        $where['OR']=['full_num[>]receive_num'];
        $where['OR']['full_num']=0;
        $packet_ar = $this->packet_M->have($where);
        empty($packet_ar) && error('红包已领完',400);
        if($packet_ar['limit_lv']>0){
            if($packet_ar['limit_lv']>$GLOBALS['user']['rating']){
                error('未达到指定等级',400);
            }
        }
        if($packet_ar['limit_num']>0){
            $count_where['uid'] = $GLOBALS['user']['id'];
            $count_where['packet_id'] = $packet_ar['id'];
            $my_num = $this->packet_M->new_count($count_where);
            if($packet_ar['limit_num'] <= $my_num){
                error('已经达到领取上限了',400);
            };
        }
        $is_where['uid']=$GLOBALS['user']['id'];
        $is_where['packet_id']=$packet_ar['id'];
        $is_where['is_use']=0;
        $is_where['end_time[>]']=time();
        $ar=$this->coupon_M->is_have($is_where);
      
        if($ar){
            error('已领取',400);
        }

        $data['uid'] = $GLOBALS['user']['id'];
        $data['desc'] = $packet_ar['title'];
        $data['money'] = $packet_ar['money'];
        $data['sid'] = $packet_ar['cdn_sid'];
        $data['pid'] = $packet_ar['cdn_pid'];
        $data['xfm'] = $packet_ar['cdn_xfm'];
        $data['source'] = $source;
        $data['begin_time'] = time();
        $data['end_time'] = time()+ $packet_ar['lifetime']*86400;
        $data['packet_id'] =  $packet_ar['id']; //不以这个类型id为主
        $this->coupon_M->save_by_oid($data);        
        //红包数量变动
        $receive_num = $packet_ar['receive_num'] + 1;  
        if($receive_num<=$packet_ar['full_num'] || $packet_ar['full_num']==0){
            $change['receive_num'] = $receive_num;
            $res = $this->packet_M->up($packet_ar['id'],$change);
            empty($res) && error('发放红包失败');
        }
        return true;      
    }

    //订单确认页，可用红包
    public function available($car_ar)
    {
        $uid=$GLOBALS['user']['id'];
        $where1['uid']=$uid;
        $where1['is_use']=0;
        $where1['begin_time[<]']=time();
        $where1['end_time[>]']=time();
        $res_ar=$this->coupon_M->lists_all($where1);
        foreach($res_ar as &$red){
            $red['is_cat']=0;
            foreach($car_ar as $vo){
                if($red['sid']==$vo['info']['sid'] && $red['pid']==0){
                    if($red['xfm']<=$vo['info']['sum_price']){
                        $red['is_cat']=1;
                        $red['pid']=$vo['data'][0]['pid'];
                        $red['limit_lv_cn']='店铺满'.$red['xfm'].'元使用';  
                        continue;
                    }else{
                        $red['limit_lv_cn']='店铺商品未满'.$red['xfm'].'元';  
                    }
                }else{
                    foreach($vo['data'] as $vos){
                        if($red['pid']==$vos['pid']){
                            if($red['xfm']<=$vos['pro']['sum_price']){
                                $red['is_cat']=1;
                                $red['pid']=$vos['pid'];
                                $red['limit_lv_cn']='指定商品满'.$red['xfm'].'元使用';  
                                continue;
                            }else{
                                $red['limit_lv_cn']='指定商品未满'.$red['xfm'].'元';  
                            }
                        }else{
                            $red['limit_lv_cn']='指定商品使用';
                        }
                    }
                }
            }
        }
        return $res_ar;
    }

   
    
    /*红包发放=领红包 title:红包中文*/
    public function get_coupon($uid,$title){
        $packet_ar = $this->packet_M->find_by_title($title);
        empty($packet_ar) && error('红包已下架',400);
        //限领
        $limit_num = $packet_ar['limit_num'];
        $where['uid'] = $uid;
        $where['packet_id'] = $packet_ar['id'];
        $my_num = $this->coupon_M->new_count($where);
        if($limit_num <= $my_num){
            error('已经达到领取上限了',400);
        };
        $data['uid'] = $uid;
        $data['desc'] = $title;
        $data['money'] = $packet_ar['money'];
        $data['sid'] = $packet_ar['cdn_sid'];
        $data['pid'] = $packet_ar['cdn_pid'];
        $data['xfm'] = $packet_ar['cdn_xfm'];
        $data['begin_time'] = time();
        $data['end_time'] = time()+ $packet_ar['lifetime']*86400;
        $data['packet_id'] =  $packet_ar['id']; //不以这个类型id为主
        $this->coupon_M->save_by_oid($data);        
        //红包数量变动
        $receive_num = $packet_ar['receive_num'] + 1;  
        if($receive_num<=$packet_ar['full_num'] || $packet_ar['full_num']==0){
            $change['receive_num'] = $receive_num;
            $res = $this->packet_M->up($packet_ar['id'],$change);
            empty($res) && error('发放红包失败');
        }
        return true;      
    }



    //coupon表内oid是来源oid type(xf/pj)  消费与评价红包发放
    public function packet_xf_pj($uid,$type,$oid='',$order_product_id=0){
        empty($uid) && error('参数丢失',400);
        empty($type) && error('参数丢失',400);      
        if($type=='xf' && empty(renew_c('is_open_xf'))){
            return false;
        }
        if($type=='pj' && empty(renew_c('is_open_pj'))){
            return false;
        }
        $packet_M = new \app\model\packet();
        $coupon_M = new \app\model\coupon();
        $rating = user_info($uid,'rating');
        if($type=='xf'){
            $where['is_xf'] = 1;
            $where['xf_rating[<=]'] = $rating;
            $where['limit_lv[<=]'] = $rating;
        }
        if($type=='pj'){
            $where['is_pj'] = 1;
            $where['pj_rating[<=]'] = $rating;
            $where['limit_lv[<=]'] = $rating;   //同时大于两个等级才可以领
        }   
        $ar = $packet_M ->lists_all($where);
        $new_ar = [];
        foreach($ar as $key=>$one){   

            $limit_num = $one['limit_num'];   
            $my_num = $coupon_M->new_count(['uid'=>$uid,'packet_id'=>$one['id']]);
            if($limit_num <= $my_num){ //限领
                continue;
            };

            if($one['full_num'] > $one['receive_num']){    
                $data_a['receive_num'] = intval($one['receive_num']) + 1;
                $packet_M->up($one['id'],$data_a); //已领取数加一
                $data['money'] = $one['money'];
                $data['sid'] = $one['cdn_sid'];
                $data['pid'] = $one['cdn_pid'];
                $data['xfm'] = $one['cdn_xfm'];
                $data['begin_time'] = time();
                $data['end_time'] = time() + $one['lifetime']*24*3600;
                $data['packet_id'] = $one['id'];
                $data['uid'] = $uid;
                $data['oid'] = $oid;
                if($type=='xf'){
                    $data['source'] = $one['xf_desc'];
                }
                if($type=='pj'){
                    $data['source'] = $one['pj_desc'];
                } 
                $data['order_product_id'] = $order_product_id; 
                $res = $coupon_M->save_by_oid($data);  

                if(empty($res)){return [];}

                $new_ar[$key]['coupon_title'] = $one['title'];
                $new_ar[$key]['money'] = $one['money'];
                if($type=='xf'){
                    $new_ar[$key]['desc'] = $one['xf_desc'];
                }
                if($type=='pj'){
                    $new_ar[$key]['desc'] = $one['pj_desc'];
                }                
                $new_ar[$key]['xfm'] = $one['cdn_xfm'];
                $new_ar[$key]['end_time'] = time() + $one['lifetime']*24*3600;
            } 
        }

        $my_ar = [];
        foreach($new_ar as $one){
            $my_ar[] = $one;
        }

        return $my_ar;
    }
    
}