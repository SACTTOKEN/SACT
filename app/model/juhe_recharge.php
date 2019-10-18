<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-09 22:56:58
 * Desc: 话费/流量/油卡
 */


namespace app\model;

class juhe_recharge extends BaseModel
{
    public $title = 'juhe_recharge';

    public function save_by_oid($data){
        $data['created_time'] = time();
        $data['update_time'] = time();
        $this->insert($this->title,$data);   
        $id= $this->id();  
        $oid = 'J'.date('Ymdhis').rand(10000,99999).$id;
        $this->update($this->title,['oid'=>$oid],['id'=>$id]); 
        $do = $this->find($id);
        if($do){return $do;}else{return 0;}  
    }
    
}
