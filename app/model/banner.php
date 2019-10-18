<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: banner广告模型
 */
namespace app\model;


class banner extends BaseModel
{
    public $title = 'banner';

    /**
     * 模型查找id数据是否存在
     * @param id 数字
     * @return bool 布尔值
     */
    public function is_find($iden){
        $data=$this->has($this->title,["AND"=>['iden'=>$iden]]);
        return $data;      
    }


	/**
     * 模型查找id数据
     * @param id 数字
     * @return data 返回一条数据
     */
	public function find($iden,$field='*'){
    	$data=$this->get($this->title,$field,["AND"=>['iden'=>$iden]]);
        return $data;      
    }



	/**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function lists_all($cate=[],$field='*'){      
        $data=$this->select($this->title,['iden','cate','title','piclink','links','description','show','sort'],["AND"=>["cate"=>$cate,'show'=>1],'ORDER'=>["sort"=>"DESC"]]);    
        return $data;      
    }

    public function list_cate($cate){      
        $data=$this->select($this->title,['id','cate','title','piclink','links','show'],["AND"=>["cate"=>$cate],'ORDER'=>["sort"=>"DESC"]]);    
        return $data;      
    }

    public function list_show($cate){      
        $data=$this->select($this->title,['id','cate','title','piclink','links','show'],["AND"=>["cate"=>$cate,"show"=>1],'ORDER'=>["sort"=>"DESC"]]);    
        return $data;      
    }

    public function list_page($id){      
        $data=$this->select($this->title,['id','cate','title','piclink','links','show','description(desc)'],["AND"=>["aid"=>$id]]);    
        return $data;      
    }

  
    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up($iden,$data){
        $data['update_time'] = time();
		$this->update($this->title,$data,['iden'=>$iden]);
		return $this->doo();
    }

    public function up_byid($id,$data){
        $data['update_time'] = time();
        $this->update($this->title,$data,['id'=>$id]);
        return $this->doo();
    }


    /**
     * 模型删除数据规则
     * @param data 数据
     * @return BOOL
     */
    public function del($iden){
        $this->delete($this->title,['iden'=>$iden]);
        return $this->doo();
    }

    public function del_byid($id){
        $this->delete($this->title,['id'=>$id]);
        return $this->doo();
    }

    public function del_by_cate($cate){
        $this->delete($this->title,['cate'=>$cate]);
        return $this->doo();
    }
    
	
}
