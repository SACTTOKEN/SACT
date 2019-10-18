<?php
/**
 * Created by yayue_god
 * User: yayue
 * Date: 2018/12/13
 * Desc: DEMO模型
 */
namespace app\model;

class plugin_guess_lord extends BaseModel
{
    public $title = 'plugin_guess_lord';

    public function find_by_stage($stage,$field='*'){
        $data=$this->get($this->title, $field, ["AND"=>['stage'=>$stage]]);
        return $data;      
    }


    /*当前期号*/
    public function stage_now(){
        $now = time();
        $where_base = ["begin_time[<]"=>$now,"end_time[>]"=>$now];
        $where_other = ['ORDER'=>["id"=>"DESC"]];
        $where = array_merge($where_base,$where_other);
        $data=$this->get($this->title,'*',$where);
        $data = $data ? $data : '';
        return $data;
    }


    /*当本期已结束下期未开始时，查本期*/
    public function stage_mid(){
        $where_base['end_time[<]'] = time();
        $where_other = ['ORDER'=>["id"=>"DESC"]];
        $where = array_merge($where_base,$where_other);
        $data=$this->get($this->title,"*",$where);
        $data = $data ? $data : '';
        return $data;
    }

    /*添加新期号时，查找是否有上一期，有则把本期开始时间begin_time，写入上期的next_begin_time字段*/
     public function save_plus($data){
        $data['created_time'] = time();
        $data['update_time'] = time();
        $this->insert($this->title,$data);            
        $new_id = $this->id();
        if(isset($data['begin_time'])){
            $where['id[<]'] = $new_id;
            $where['LIMIT'] =1;
            $where['ORDER'] = ["id"=>"DESC"];
            $old_id = $this->get($this->title,'id',$where);
            $this->update($this->title,['next_begin_time'=>$data['begin_time']],['id'=>$old_id]);
        }
        return $new_id;  
    }



   
    
}
