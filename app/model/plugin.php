<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-03 15:48:35
 * Desc: 插件
 */
namespace app\model;


class plugin extends BaseModel
{
    public $title = 'plugin';


    public function find_open($iden)
    {
        $data = $this->get($this->title, 'is_open', ["AND" => ['iden' => $iden]]);
        return $data;
    }

    public function links()
    {
        $where['is_open']=1;
        $where['links[!]']='';
        $where['ORDER']=["sort" => "DESC"];
        $data = $this->select($this->title, ['iden', 'title', 'links(url)'], $where);
        $data = array_column($data, null, 'iden');
        return $data;
    }

    public function open_status()
    {
        $data = $this->select($this->title, ['iden', 'is_open', 'title'], ['ORDER' => ["cate" => "DESC"]]);
        return $data;
    }


    public function up($id, $data)
    {
        $this->update($this->title, $data, ['id' => $id]);
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

}
