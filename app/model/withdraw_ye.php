<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 14:17:24
 * Desc: 提现模型
 */
namespace app\model;


class withdraw_ye extends BaseModel
{
    public $title = 'withdraw_ye';

    /*生成带单号的记录,写入的表内必须有oid字段*/
    public function save_by_oid($data){
        $data['created_time'] = time();
        $data['update_time'] = time();
        $this->insert($this->title,$data);   
        $id= $this->id();  
        $oid = 'W'.date('Ymdhis').rand(1000,9999).$id;
        $this->update($this->title,['oid'=>$oid],['id'=>$id]); 
        $do = $this->find($id);
        if($do){return $do;}else{return 0;}  
    }
}
