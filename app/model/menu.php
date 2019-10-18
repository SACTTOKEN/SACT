<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:20:52
 * Desc: 栏目模型
 */
namespace app\model;

class menu extends BaseModel
{
    public $title = 'admin_menu';
    

    /**
     * 模型删除数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function del($id){
        $is_have_son=$this->has($this->title,["AND"=>['parent_id'=>$id]]);
        if($is_have_son){
            error('请先删除子栏目',400);
        }
        $this->delete($this->title,['id'=>$id]);
        return $this->doo();  
    }

    /**
     * 栏目根
     * @param  data 数据
     * @return 上级信息
     */
    public function up_id($menu_id=0){
        $parent_id = $this->find($menu_id,'parent_id');
        return $parent_id;
    }



    /**
     * 根据control 和 action 查menu 的id
     */
    public function find_id($control,$action){
        $data = $this->select($this->title,'id',["AND"=>['control'=>$control,'action'=>$action]]);
        return $data[0];
    }


    /**
     * 根据control 和 action 查menu 的id
     */
    public function sort($ar,$parent_id=0){
        $i=1;
        $flag = true;
        foreach($ar as $one){
            $data['sort'] = $i;
            $res = $this->up($one,$data);
            empty($res) && $flag = false;
            $i++;
        }
        return $flag;    
    }

    /**
     * 栏目树
     * @param data 数据
     * @return 下级信息
     */
    public function tree($pid=0){
        $parent_title = $this->find($pid,'title');
        $where['AND']['parent_id']=$pid;
        if($GLOBALS['admin']['role_con'] == 'god'){
        }elseif($GLOBALS['admin']['role_con'] == 'kind'){
            $where['AND']['show']=1;
        }else{
            $where['AND']['id']=explode(',',$GLOBALS['admin']['role_con']);
        }
        $where['ORDER']=["sort"=>"DESC"];
       
        $data = $this->select($this->title,'*',$where);
        foreach($data as &$rs){
            $rs['parent_title'] = $parent_title;
        }
        unset($rs);
        return $data;
    }

    
    /**
     * 栏目树 不限制权限
     * @param data 数据
     * @return 下级信息
     */
    public function tree_free($pid=0){
        $parent_title = $this->find($pid,'title');
        $where['AND']['parent_id']=$pid;
        $where['AND']['show']=1;
        $where['ORDER']=["sort"=>"DESC"];
       
        $data = $this->select($this->title,'*',$where);
        foreach($data as &$rs){
            $rs['parent_title'] = $parent_title;
        }
        unset($rs);
        return $data;
    }

    /**
     * 栏目树 不限制权限
     * @param  上级ID
     * @return 下级ID集合 array
     */
    public function tree_down_id($pid=0){
        $parent_title = $this->find($pid,'title');
        $where['AND']['parent_id']=$pid;
        $where['AND']['show']=1;
        $where['ORDER']=["sort"=>"DESC"];
       
        $data = $this->select($this->title,'id',$where);
        return $data;
    }



    
    /**
     * 权限
     */
    public function competence($url,$role_con){
        if($role_con=='kind'){
            $where['show']=1;
            $where['OR']=[
                'url'=>$url,
                'url_details[~]'=>$url
            ];
            $data=$this->has($this->title,$where);
        }else{
            $where['show']=1;
            $where['OR']=[
                'url'=>$url,
                'url_details[~]'=>$url
            ];
            $where['id']=explode(',',$GLOBALS['admin']['role_con']);
            $data=$this->has($this->title,$where);
        }
        return $data;      
    }



    /*父级ID串*/
    public function find_father($cate_id,$ar=[]){
        $parent_id  = $this->get($this->title,'parent_id',['id'=>$cate_id]); 
        if($parent_id==0){
            return array_reverse($ar);
        }else{
            $ar[] = $parent_id;
            return $this->find_father($parent_id,$ar);
        }
    }



    /**
     * 是否有小类
     * @param data 数据
     * @return BOOL
     */
    public function find_cate_son($id){
        $is_have_son = $this->get($this->title,['id'],['parent_id'=>$id]);
        if($is_have_son){
            return TRUE;
        }else{
            return FALSE;
        }
    }











  
	
}
