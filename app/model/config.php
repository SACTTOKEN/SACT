<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 10:13:40
 * Desc: 配置模型
 */
namespace app\model;
use core\lib\redis;

class config extends BaseModel
{

    public $title = 'config';
    
    public function find($id,$field='value'){
        $data=$this->get($this->title, $field, ["AND"=>['iden'=>$id]]);
        return $data;      
    }

    public function is_find($id){
		$data=$this->has($this->title,["AND"=>['iden'=>$id]]);
        return $data;      
    }
    
   /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up($id,$data=[]){
        $data['update_time'] = time();
        $this->update($this->title,$data,['iden'=>$id]);
        return $this->doo(); 
    }



    /**
     * 指定cate里的iden是否存在
     * @param idend 数字  cate 类别
     * @return bool 布尔值
     */
    public function is_find_bycate($iden,$cate){
        $data = $this->has($this->title,["AND"=>['iden'=>$iden,'cate'=>$cate]]);
        return $data;
    }



    public function lists_all($cate=[],$field='*'){      
        $data=$this->select($this->title,['id','iden','title','help','value','types','cate','sort','yz'],["cate"=>$cate,'ORDER'=>["sort"=>"DESC"]]);       
        return $data;      
    }

    /**
     * vue 首页设置用到
     * @param data 数据
     * @return 首页的cate 注册到config的iden里。用来控制显隐。value值为0和1。
     */
    public function list_cate($cate){
        $data = $this->select($this->title,['iden','title','value'],["cate"=>$cate]); 
        return $data; 
    }

    
    public function list_cate_iden($cate){
        $data = $this->select($this->title,['iden','value'],["cate"=>$cate]); 
        $data= array_column($data, 'value', 'iden');
        return $data; 
    }


    /**
     * 排序
     */
    public function sort($ar,$cate=0){
        $i=1;
        $flag = true;
        foreach($ar as $one){
            $data['sort'] = $i;
            $data['update_time'] = time();
            $res = $this->update($this->title,$data,['id'=>$one]);
            $i++;
        }
        return $flag;    
    }



	
}
