<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-01-31 11:20:42
 * Desc: 奖励名称模型
 */
namespace app\model;

class reward extends BaseModel
{
    public $title = 'reward';

   
    /**
     * 模型查找id数据
     * @param id 数字
     * @return data 返回一条数据
     */
    public function find_title($title){
        $data=$this->get($this->title,'iden',["AND"=>['title'=>$title]]);
        return $data;      
    }
    
    /**
     * 通过iden查title,先查redis
     * @param iden 如：sjfx
     * @return data 返回某个字段的值 如：三级分销
     */
    public function find_redis($iden){
        $data = $this->get($this->title, 'title',["AND"=>['iden'=>$iden]]);
        return $data;
    }

    /**
     * 已知中文title查iden
     * @param  [type] $cn [description]
     * @return [type]  $iden     [description]
     */
    public function find_iden($cn){
        $iden = $this->get($this->title,'iden',["AND"=>['title'=>$cn]]);
        return $iden;
    }


    /**
     * 查模型列表数据
     * @param  无
     * @return data 返回数据集
     */
    public function lists_all($where=[],$field='*'){      
        $data=$this->select($this->title,$field,['ORDER'=>['types'=>"DESC","id"=>"ASC"]]);
        $type_ar = ['0'=>'奖励','1'=>'%奖励%','2'=>'金额'];  
        foreach($data as &$rs){
            $types = $rs['types'] ? $rs['types'] : 0;
            $rs['types_cn'] = $type_ar[$types];
        }
        unset($rs);
        return $data;     
    }

     /**
     * 无分页列表数据
     * @param  无
     * @return data 返回数据集
     */
    public function reward_lists_all($where=[],$field='*'){      
        $data=$this->select($this->title,$field,$where);        
        return $data;     
    }

    /**
     * 奖励类型 下拉列表数据VUE用
     * @param  无
     * @return data 返回数据集
     */
    public function option(){
        $data=  array(
                ['value'=>'0','label'=>'奖励'],
                ['value'=>'2','label'=>'金额'],
                    );  
        return $data;
    } 


    public function option2(){
        if(!plugin_is_open('xnbkj')){
            $where['iden[!]']=['coin','coin_storage'];
        }
        $where['AND']['iden[!]']=['USDT','BTC','ETH','LTC','BCH','integrity'];
        $where['show']=1;
        $where['types']=2;
        $data=$this->reward_lists_all($where,'iden');
        $data_ar=array();
        foreach($data as $key=>&$vo){
            $data_ar[$key]['value']=$vo;
            $data_ar[$key]['label']=find_reward_redis($vo);
        }
        return $data_ar;
    } 


    public function option2_ar(){
        $data=$this->option2();
        $ar = array_column($data, 'label', 'value');
        return $ar;
    } 

   
    /**
     * 查types=2 为金额的奖励名称
     * @param  $types
     * @return data
     */
    public function title_by_types($types){
        $data = $this->select($this->title,['title','iden'],['types'=>$types,'show'=>1]);
        return $data;
    }



    
}
