<?php
/**
 * Created by yayue_god
 * User: yayue
 * Date: 2018/12/13
 * Desc: DEMO模型
 */
namespace app\model;

class plugin_guess_slave extends BaseModel
{
    public $title = 'plugin_guess_slave';

    /**
     * 模型查找id数据
     * @param id 数字 field 字段名
     * @return data 返回某个字段的值
     */
    public function add_rf($stage,$rf){
        $data = $this->update($this->title,['rf'=>$rf],['stage'=>$stage]);
        return $this->doo(); 
    }

  


    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function list_cate($category_id){      
        $data=$this->select($this->title,"*",["category_id"=>$category_id,'ORDER'=>["sort"=>"DESC"]]);        
        return $data;     
    }

    /**
     * 模型列表数据
     * @param  无
     * @return data 返回数据集
     */
    public function lists_all($stage=[],$field='*'){      
        $data=$this->select($this->title,"*",['stage'=>$stage]);        
        return $data;     
    }

  
    /**
     * 当期买涨总数
     */
    public function sum_up($stage){    
        $data=$this->sum($this->title,'buy_up',['stage'=>$stage]);
        $data = $data ? $data : 0;
        return $data;
    }

    /**
     * 当期买跌总数
     */
    public function sum_down($stage){    
        $data=$this->sum($this->title,'buy_down',['stage'=>$stage]);
        $data = $data ? $data : 0;
        return $data;
    }

    public function ranking(){
       $data = self::$medoo->query("select count(*) as win_num,uid,sum(earn) as win from plugin_guess_slave where earn>0 group by uid order by win desc")->fetchAll();
       return $data;
    }

    /*某期某人记录*/
    public function buy_info($uid,$stage){
        $where['uid'] = $uid;
        $where['stage'] = $stage;
        $data=$this->get($this->title, '*', ["AND"=>['uid'=>$uid,'stage'=>$stage]]);
        return $data;
    }

    /*按stage改结算状态*/
    public function change_by_stage($stage){
        $where['stage'] = $stage;
        $this->update($this->title,['is_end'=>1,'update_time'=>time()],$where);
        return $this->doo(); 
    }

    

        /*返回单位时间内胜的次数最多*/
        public function join_top($begin_time,$end_time){
        $data = self::$medoo->query("select count(*) as join_num,uid from plugin_guess_slave where created_time>".$begin_time." and update_time<".$end_time." and earn>0 group by uid order by join_num desc limit 50")->fetchAll();
        return $data;
        } 

        /*返回单位时间内指定用户胜的次数*/
        public function win_num($uid,$begin_time,$end_time){
        $data = self::$medoo->query("select count(*) as win_num,uid from plugin_guess_slave where created_time>".$begin_time." and created_time<".$end_time." and uid=".$uid." and earn>0")->fetch();
        return $data['win_num'];
        }


    
   
    
}
