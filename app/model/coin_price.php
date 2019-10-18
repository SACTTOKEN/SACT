<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-27 15:30:00
 * Desc: 币价管理模型
 */
namespace app\model;

class coin_price extends BaseModel
{
    public $title = 'coin_price';
    
	
    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists($page=1,$number=10,$where_base=[]){        
        $startRecord=($page-1)*$number;        
        $where_other = ['ORDER'=>["effective_time"=>"DESC","id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        $data=$this->select($this->title,'*',$where_ar);
        
        return $data;
    }


	/**
     * 模型当前币价
     * @return data 返回一条数据
     */
    public function price(){
        $where['effective_time[<]'] = time();
        $where['ORDER']=['effective_time'=>'DESC','id'=>'DESC'];
    	$data=$this->get($this->title,'price',$where);
        return $data;      
    }


	/**
     * 模型当前币价
     * @return data 返回一条数据
     */
    public function price_all(){
        $where['effective_time[<]'] = time();
        $where['ORDER']=['effective_time'=>'DESC','id'=>'DESC'];
        $where['LIMIT']=[0, 4];
    	$data=$this->select($this->title,'price',$where);
        return $data;      
    }


	/**
     * 模型当前币价
     * @return data 返回一条数据
     */
    public function price_list(){
        $where['effective_time[<]'] = time();
        $where['ORDER']=['effective_time'=>'DESC','id'=>'DESC'];
        $where['LIMIT']=[0, 7];
    	$data=$this->select($this->title,['price','effective_time'],$where);
        return $data;      
    }

}
