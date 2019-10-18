<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: admin管理员用户模型
 */
namespace app\model;

class admin extends BaseModel
{
    public $title = 'admin';
    
	/**
     * 模型查找id数据
     * @param id 数字
     * @return data 返回一条数据
     */
    public function find($id,$field=['id','role_id','show','role_con','username','service','im','tel','nick_name']){
    	$data=$this->get($this->title,$field,["AND"=>['id'=>$id]]);
        return $data;      
    }

    public function find_one($id,$field=['id','role_id','show','role_con','username','service','im','tel','nick_name'])
    {
        $data=$this->get($this->title,$field,["AND"=>['id'=>$id]]);
        return $data;      
    }

	/**
     * 模型列表数据
     * @param 
     * @return data 返回数据集
     */
    public function lists_all($where_base=[],$field='*'){
    	$data=$this->select($this->title,['id','username','role_id','show','last_login','last_ip','tel','service','nick_name'],['role_con[!]'=>'god']);      
        return $data;      
    }

	/**
     * 模型列表数据
     * @param 
     * @return data 返回数据集
     */
    public function lists_all2($where_base=[],$field='*'){
    	$data=$this->select($this->title,$field,$where_base);      
        return $data;      
    }
	
	

    /**
     * 验证管理员账号密码
     * @param  param1:账号,param2:密码
     * @return 存在返回存入redis的信息，不存在返回NULL
     */
    public function check_user($ad,$pw){
        $where = ["AND"=>['username'=>$ad,'password'=>$pw]];
        $data=$this->get($this->title,['id','role_id','show','role_con','username','service','tel','im','nick_name'],$where);
        if(empty($data)){error("用户名或密码错误!",404);}
        if($data['show']!=1){error("请联系管理员",401);}
        return $data;        
    }


    /**
     * 模型修改role_con
     * @param  role_id  role_con
     * @return BOOL
     */
    public function up_role_con($role_id,$ar){
        $data['update_time'] = time();
        if(is_array($ar)){
            $data['role_con'] = implode(',',$ar);
        }else{
            $data['role_con'] = $ar;
        }
        $this->update($this->title,$data,['role_id'=>$role_id]);
        return $this->doo();
    }


    public function find_by_tel($tel){
       $data=$this->get($this->title,['id','role_id','show','role_con','username'],["AND"=>['tel'=>$tel]]);
       if(empty($data)){error("管理员不存在!",404);}
       if($data['show']!=1){error("请联系管理员",401);}
       return $data;
    }
	
}
