<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-05 09:50:15
 * Desc: 流水模型
 */
namespace app\model;


class money extends BaseModel
{
    public $title = 'money';
    public $title2 = '';

    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        if($data['balance']<0){
            return false;
        }
        $data['created_time'] = time();   
        $this->insert($this->title,$data);    
		if($this->doo()){
        $this->getname($data['uid']);
        $this->insert($this->title2,$data);   
        }          
        return $this->doo();
    }


    /**
     * 模型查找id数据是否存在
     * @param where 条件
     * @return bool 布尔值
     */
    public function money_one($where){
        $this->getname($where['uid']);
        $where['ORDER']=["id"=>"DESC"];
		$data=$this->get($this->title2,'*',$where);
        return $data;      
    }

    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists_one($uid,$page=1,$number=10,$where_base=[]){
        $this->getname($uid);
        $startRecord=($page-1)*$number;        
        $where_other = ['ORDER'=>["id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        $where_base['uid']=$uid;
        $where = array_merge($where_base,$where_other);
     
        $data_ar=$this->select($this->title2,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        $data=$this->select($this->title2,'*',$where_ar);

        return $data;
    }

    /**
     * 获取某一类条数，不传值返回总条数
     * @param uid 用户id
     * @return int 条数
     */
    public function new_count_one($uid,$where_base=[]){
        $this->getname($uid);
        $where_base['uid']=$uid;
        $data=$this->count($this->title2,$where_base);
        $data = $data ? $data : 0;
        return $data;
    }


    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists($page=1,$number=10,$where_base=[]){        
        $startRecord=($page-1)*$number;        
        $where_other = ['ORDER'=>["id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
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
     * 获取某一类条数，不传值返回总条数
     * @param uid 用户id
     * @return int 条数
     */
    public function new_count($where_base=[]){
        $data=$this->count($this->title,$where_base);
        $data = $data ? $data : 0;
        return $data;
    }


    /*按订单号查资金流水*/
    public function lists_by_oid($oid){ 
        $where_base = ['oid'=>$oid];
        $where_other = ['ORDER'=>["id"=>"DESC"]];
        $where = array_merge($where_base,$where_other);
        $data=$this->select($this->title,'*', $where);  
        return $data;
    }

    /*导出*/
    public function list_excel($field){      
        $data=$this->select($this->title,$field,['ORDER'=>["id"=>"DESC"]]);        
        return $data;     
    }

    /*求和*/
    public function find_sum($field,$where=[]){
        $this->getname($where['uid']);
        $data=$this->sum($this->title2,$field,$where);
        $data = $data ? $data : 0;
        return $data;
    }
    

    /*求和*/
    public function all_find_sum($field,$where=[]){
        $data=$this->sum($this->title,$field,$where);
        $data = $data ? $data : 0;
        return $data;
    }
    

    /*赋值表名不存在则新建表*/
    public function getname($uid){
        $last_num = substr($uid,'-1');
        if($last_num == 0){$last_num = 10;}
        $table=$this->title.'_'.$last_num;
        $res=self::$medoo->query("SELECT table_name FROM information_schema.TABLES WHERE table_name ='".$table."'")->fetchAll();
		if(!$res){
	    	self::$medoo->query("create table ".$table." like ".$this->title."");
        }
        $this->title2=$table;
    }

   
}
