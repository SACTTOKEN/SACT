<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: page页面
 */
namespace app\model;


class page extends BaseModel
{
    public $title = 'page';

    public function copy($iden)
    {
        $sql="insert into ".$this->title."(iden,is_show,title,title_en,style,types) select '".$iden."',1,title,title_en,style,types from ".$this->title." as a where a.iden='home'";
        $this->query($sql)->fetchAll();        
        $info=$this->lists_all(['iden'=>$iden,'types[!]'=>'module'],['id','iden','is_show','title','title_en','style','types']);
        return $info;
    }
}