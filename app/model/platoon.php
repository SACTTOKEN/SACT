<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 支付流水
 */
namespace app\model;

class platoon extends BaseModel
{
    public $title = 'platoon';

    /*生成带单号的记录,写入的表内必须有oid字段*/
    public function save_by_oid($data){
        $data['created_time'] = time();
        $data['update_time'] = time();
        $this->insert($this->title,$data);   
        $id= $this->id();  
        $oid = 'P_'.$id;
        $this->update($this->title,['oid'=>$oid],['id'=>$id]); 
        $do = $this->find($id);
        if($do){return $do;}else{return 0;}  
    }
}
