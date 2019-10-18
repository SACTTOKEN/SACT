<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-10 11:08:23
 * Desc: 早起签到模型
 */
namespace app\model;

class plugin_early_lord extends BaseModel
{
    public $title = 'plugin_early_lord';

    public function is_find_stage($id,$stage){
        $data=$this->has($this->title,["AND"=>['id'=>$id,'stage'=>$stage]]);
        return $data;      
    }

    public function find_by_stage($stage,$field='*'){
        $data=$this->get($this->title, $field, ["AND"=>['stage'=>$stage]]);
        return $data;      
    }

    /*上期签到期号,已签 */  
    public function stage_pre($stage_now_id){
        // $where_base = ["is_end"=>1];   
        // $where_other = ['ORDER'=>["stage"=>"DESC",'id'=>"DESC"]];  
        //注：以上这样判断会出现8：40分结算时，如把8点到8点40之间已报名的人也结算掉。这些人的上一期就是当期，出现前台显示未结算也能显示出已结算的金额。
        $where_base = ['id[<]'=>$stage_now_id]; 
        $where_other = ['ORDER'=>["stage"=>"DESC",'id'=>"DESC"]];  
        $where = array_merge($where_base,$where_other);
        $data = $this->get($this->title,'stage',$where);
        $data = $data ? $data : '';
        return $data;
    }
    
    /*最新期号，未签*/
    public function stage_now(){

        renew_c('early_time');
        $early_time = c('early_time');
        
        $early_time_ar =  explode('|',$early_time);
        $early_begin_time =  $early_time_ar[0];;
        $early_end_time =  $early_time_ar[1];;

        $early_begin_time = $early_begin_time ? $early_begin_time : '06:00:00';
        $early_end_time = $early_end_time ? $early_end_time : '08:00:00';

        $now_time = time();//1555925762
        $begin = strtotime($early_begin_time);  //1555884000
        $end = strtotime($early_end_time); 

        // if($now_time<$begin){
        //     $stage_now = date('Ymd');
        // }

        if($now_time>=$end){
            $next = intval($end) + 3600*24;
            $stage_now = date('Ymd',$next);
        }else{
            $stage_now = date('Ymd');
        }
        return  $stage_now;
    }

    /*签到时间搓*/
    public function sign_time(){

        $early_time = c('early_time');
        $early_time_ar =  explode('|',$early_time);
        $early_begin_time = $early_time_ar[0];
        $early_end_time =  $early_time_ar[1]; //08:00 

        $early_begin_time = $early_begin_time ? $early_begin_time : '06:00:00';
        $early_end_time = $early_end_time ? $early_end_time : '08:00:00';

        $now_time = time();
        $begin = strtotime($early_begin_time);  //1554847200
        $end = strtotime($early_end_time); 

        if($now_time<$begin){
            $ar['begin'] = $begin;
            $ar['end'] = $end;
        }
        if($now_time>$end){
            $ar['begin'] = intval($begin) + 3600*24;
            $ar['end'] = intval($end) + 3600*24;
        }
        return $ar;
    }



    /*是否可以投注*/
    public function stage_is_buy(){  
        $early_time = c('early_time');
        $early_time_ar =  explode('|',$early_time);
        $early_begin_time = $early_time_ar[0];
        $early_end_time  =   $early_time_ar[1]; //08:00 


        $early_begin_time = $early_begin_time ? $early_begin_time : '06:00:00';
        $early_end_time = $early_end_time ? $early_end_time : '08:00:00';

        $now_time = time();
        $begin = strtotime($early_begin_time);  //1554847200
        $end = strtotime($early_end_time); 

        if($end >=$now_time && $now_time >= $begin){
            return false;
        }else{
            return true;
        }
    }

    /*是否签到时间*/
    public function stage_is_sign() {  

        $early_time = c('early_time');
        $early_time_ar =  explode('|',$early_time);
        $early_begin_time = $early_time_ar[0];
        $early_end_time  =   $early_time_ar[1]; //08:00 

        $early_begin_time = $early_begin_time ? $early_begin_time : '06:00:00';
        $early_end_time = $early_end_time ? $early_end_time : '08:00:00';

        $now_time = time();
        $begin = strtotime($early_begin_time);  //1554847200
        $end = strtotime($early_end_time); 

        if($end >=$now_time && $now_time >= $begin){
            return true;
        }else{
            return false;
        }
    }



    /*赛季排行*/
    public function war($page=1,$number=2){
        $early_war_M = new \app\model\plugin_early_war();
        $startRecord=($page-1)*$number; 
        $sql = "select * from plugin_early_war group by war order by id desc ";
        $data = self::$medoo->query($sql)->fetchAll();
        return $data;        
    }

    public function war_count(){
        $sql = "select count(*) as num from plugin_early_war group by war order by id desc ";
        $num = self::$medoo->query($sql)->fetchAll();
        return intval($num[0]['num']);        
    }



    

    
}
