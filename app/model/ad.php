<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-29 17:58:37
 * Desc: 省市区县 https://market.console.aliyun.com/imageconsole/index.htm?#/bizlist?_k=eihhhi
 */
namespace app\model;

class ad extends BaseModel
{
    public $title = 'ad';

    public function tree($pid=0){
        $where['parent_id']=$pid;    
        $data = $this->select($this->title,['title','yid','parent_id'],$where);
        unset($rs);
        return $data;
    }
}
