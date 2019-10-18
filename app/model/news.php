<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 文章模型
 */
namespace app\model;


class news extends BaseModel
{
    public $title = 'news';


    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function lists_all($cate_id=[],$field='*'){      
        $data=$this->select($this->title,"*",["cate_id"=>$cate_id,'ORDER'=>["sort"=>"DESC"]]);        
        return $data;     
    }


    public function lists_tj($where=[],$field='*'){  
        $where['piclink[!]'] = '';   
        $where['show'] = 1;   
        $where['LIMIT'] = [1,10];
        $data = $this->rand($this->title,['id','title','piclink','description','hit','created_time'],$where);     
        return $data;     
    }



    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function find_all($where){      
        $data=$this->select($this->title,['id','title','cate_id','update_time','sort','show','is_top'],$where);        
        return $data;     
    }




    public function list_excel($field){      
        $data=$this->select($this->title,$field,['ORDER'=>["id"=>"DESC"]]);        
        return $data;     
    }



    /**
     * 新闻表是否有该类别
     * @param cate_id 类别id
     * @return bool
     */
    public function is_have_cate($cate_id=''){
        $data=$this->get($this->title,['id'],["AND"=>['cate_id'=>$cate_id]]);
        if($data){
            return TRUE; 
        }else{
            return FALSE;
        }
             
    }

    public function del_old_content($id){
        $rs=$this->get($this->title,'content',["AND"=>['id'=>$id]]);
        $filepath = IMOOC.$rs['content'];
        if(file_exists($filepath)){
            unlink($filepath);
        }
    }



    



    
	
}
