<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/24
 * Desc: 用户副表模型
 */
namespace app\model;


class user_attach extends BaseModel
{
    public $title = 'user_attach';
    public $field = ['uid(id)','shop_title','shop_logo','shop_tel','shop_province','shop_city','shop_area','shop_town','shop_address','shop_cate','shop_longitude','shop_latitude','shop_wechat','shop_recommend','shop_fee','shop_referrer'];
    
    public function find_supplier($where)
    {
        $data=$this->get($this->title, $this->field, $where);
        return $data;      
    }

    public function lists_supplier($where)
    {
        $data=$this->select($this->title, $this->field,$where);
        return $data;      
    }


    /**
     * 模型查找id数据
     * @param id 数字 field 字段名
     * @return data 返回某个字段的值
     */
    public function find($id,$field='*'){
        $data=$this->get($this->title, $field, ["AND"=>['uid'=>$id]]);
        return $data;      
    }


    /**
     * 模型查找收款信息
     * @param uid 用户ID
     * @return data 返回一条数据
     */
    public function find_collections($uid){
        $data=$this->get($this->title,'*',["AND"=>['uid'=>$uid]]);
        return $data;      
    }


    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $this->insert($this->title,$data);            
        return $this->doo();  
    }


    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up($uid,$data){
        $this->update($this->title,$data,['uid'=>$uid]);
        
        $redis = new \core\lib\redis();
        $rd_name = 'user:'.$uid; 
        foreach($data as $key=>$val){
            if(strpos($key,'[+]')>0 || strpos($key,'[-]')>0){
                $key = str_replace('[+]','',$key);   
                $key = str_replace('[-]','',$key);               
                $val_now = $this->find($uid,$key);
                $redis->hset($rd_name,$key,$val_now);
            }else{
            $redis->hset($rd_name,$key,$val);
            }
        }

        return $this->doo();  
    }

    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up_all($where,$data){
        $this->update($this->title,$data,$where);
        return $this->doo();  
    }


    /**
     * 模型删除数据规则
     * @param data 数据
     * @return BOOL
     */
    public function del($uid){
        $this->delete($this->title,['uid'=>$uid]);
        return $this->doo();  
    }
	
}
