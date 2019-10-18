<?php
/**
 * Created by yayue_god
 * User: yayue
 * Date: 2018/12/13
 * Desc: DEMO模型
 */
namespace app\model;

class plugin_early_slave extends BaseModel
{
    public $title = 'plugin_early_slave';


    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $data['created_time'] = time();
        $this->insert($this->title,$data);
        $id=$this->id();  
        $oid = date('Ymdhis').rand(10000,99999).$id;
        $this->update($this->title,['oid'=>$oid],['id'=>$id]);    
        $do = $this->find($id);
        if($do){return $do;}else{return 0;}  
    }


    public function del_by_stage($stage){
        $data = $this->delete($this->title,['stage'=>$stage]);
        $do = $data->rowCount();
        return $this->doo();
    }

  
    public function lists_ranking($page=1,$number=10,$where_base=[]){        
        $startRecord=($page-1)*$number;        
        $where_other = ['ORDER'=>["id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        $data=$this->select($this->title,['stage','uid','sign_ok','stake','earn','is_end'],$where_ar);
 
        return $data;
    }

    

    /*某期某人记录*/
    public function buy_info($uid,$stage){
        $where['uid'] = $uid;
        $where['stage'] = $stage;
        $data=$this->get($this->title, '*', ["AND"=>['uid'=>$uid,'stage'=>$stage]]);
        return $data;
    }

    /*签到*/
    public function sign_me($uid,$stage){
        $where['uid'] = $uid;
        $where['stage'] = $stage;
        $data['sign_time'] = time();
        $data['sign_ok'] = 1;
        $res=$this->update($this->title, $data, $where);
        return $this->doo();
    }


    /*是否签到*/
    public function is_sign($uid,$stage){
        $where['uid'] = $uid;
        $where['stage'] = $stage;
        $where['sign_ok'] = 1;
        $res=$this->get($this->title,['id','stake'],$where);
        return $res;
    }

    /*上期结算后 起得最早，手气最好(累计赚得最多), 连续签到最多的*/
    public function ranking_old($stage){ 
        $rank_1 = [];
        $rank_2 = [];  
        $rank_3 = [];      

        $where_1 = ["AND"=>["sign_ok"=>1],"ORDER"=>["stage"=>"DESC","sign_time"=>"ASC"],"LIMIT" =>1];
        $rank_1  = $this->get($this->title,['uid','sign_time','oid'],$where_1);
      
        $rank_2 = self::$medoo->query("select uid,sum(earn) as win,oid from ".$this->title." where earn>0 and sign_ok=1 group by uid order by win desc")->fetch();

        if(!empty($rank_2)){
                unset($rank_2[0]);
                unset($rank_2[1]);
                unset($rank_2[2]);
        }

        $where_3 = ["AND"=>["sign_ok"=>1],"ORDER"=>["continu_num"=>"DESC"],"LIMIT" =>1];
        $rank_3 = $this->get($this->title,['uid','continu_num','oid'],$where_3);
        $rank[0] = $rank_1;
        $rank[1] = $rank_2;
        $rank[2] = $rank_3;
        return $rank;
    }


    /*上期结算后 签到最多 总计人数 累计打卡   偷懒最多 总计人数（并列的）  累计亏损      瓜分最多 总计人数（并列）  瓜分多少*/
    public function ranking_upone($stage){ 
        $rank_1 = [];
        $rank_2 = [];  
        $rank_3 = [];      

        $res_1 = $this->join_man_m($stage);
        $res_2 = $this->sign_man_m($stage);

        $res_1 = $res_1 ? $res_1 : 0;
        $res_2 = $res_2 ? $res_2 : 0;

        $where_2 = ["AND"=>['stage'=>$stage,'sign_ok'=>0],"ORDER"=>["stake"=>"DESC"],"LIMIT"=>1];
        $stake = $this->get($this->title,'stake',$where_2);
        $stake = $stake ? $stake : 0;
        
        if($stake!=0){
            $where_3 = ["AND"=>['stage'=>$stage,'sign_ok'=>0,'stake'=>$stake]];
            $res_3 = $this->count($this->title,'id',$where_3);
            $res_4 = $this->sum($this->title,'stake',$where_3);    
        }else{
            $res_3 = 0;
            $res_4 = 0;
        }
        
        $where_4 = ["AND"=>['stage'=>$stage,'sign_ok'=>1],"ORDER"=>["stake"=>"DESC"],"LIMIT"=>1];
        $stake_big = $this->get($this->title,'stake',$where_4);
        $stake_big = $stake_big ? $stake_big : 0;

        if($stake_big!=0){
            $where_5 = ["AND"=>['stage'=>$stage,'sign_ok'=>1,'stake'=>$stake_big]];
            $res_5 = $this->count($this->title,'id',$where_5);   
            $res_6 = $this->sum($this->title,'earn',$where_5);    
        }else{
            $res_5 = 0;
            $res_6 = 0;
        }

        $rank['1'] = $res_1;
        $rank['2'] = $res_2;
        $rank['3'] = $res_3;
        $rank['4'] = $res_4;
        $rank['5'] = $res_5;
        $rank['6'] = $res_6;
        return $rank;
    }



    /*参与总额*/
    public function join_all_m($stage){
        $data = $this->sum($this->title,'stake',['stage'=>$stage]);
        $data = $data ? $data : 0;
        return $data;       
    }

    /*签到总额*/
    public function sign_all_m($stage){
        $data = $this->sum($this->title,'stake',['stage'=>$stage,'sign_ok'=>1]);
        $data = $data ? $data : 0;
        return $data;       
    }

    /*参与人数*/
    public function join_man_m($stage){
        $data = $this->count($this->title,'id',['stage'=>$stage]);
        $data = $data ? $data : 0;
        return $data;       
    }

    /*签到人数*/
    public function sign_man_m($stage){
        $data = $this->count($this->title,'id',['stage'=>$stage,'sign_ok'=>1]);
        $data = $data ? $data : 0;
        return $data;       
    }


    /*求和*/
    public function find_sum($field,$where=[]){
        return $this->sum($this->title,$field,$where);
    }


    /*签到次数最多的,有并列的*/
    public function find_max_sign($stage){
        $data = self::$medoo->query("select uid,count(*) as win from ".$this->title." where stage like '%".$stage."%' and is_end=1 and sign_ok=1 group by uid order by win desc")->fetchAll();
        if($data){
            $max_win = 0;
            foreach($data as $one){
                if($max_win==0){$max_win = $one['win'];}         
                if($max_win==$one['win']){
                    $new_ar[] = ['uid'=>$one['uid'],'win'=>$one['win']];   
                }else{
                    break;
                }                
            }
        }else{
            $new_ar = [];
        }
        return $new_ar;
    }


    /*周签到次数最多的,有并列的*/
    public function find_max_sign_week($begin_day,$end_day){
        $data = self::$medoo->query("select uid,count(*) as win from ".$this->title." where is_end=1 and sign_ok=1 and stage between  ".$begin_day." and ".$end_day." group by uid order by win desc")->fetchAll();
        if($data){
            $max_win = 0;
            foreach($data as $one){
                if($max_win==0){$max_win = $one['win'];}         
                if($max_win==$one['win'] && $max_win!=0){
                    $new_ar[] = ['uid'=>$one['uid'],'win'=>$one['win']];   
                }else{
                    break;
                }                
            }
        }else{
            $new_ar = [];
        }
        return $new_ar;
    }


    /*签到盈利最多的,有并列的*/
    public function find_max_earn($stage){
        $data = self::$medoo->query("select uid,count(*) as win,sum(earn) as win_money from ".$this->title." where stage like '%".$stage."%' and sign_ok=1 and is_end=1 group by uid order by win_money desc")->fetchAll();
        if($data){
            $max_win_money = 0;
            foreach($data as $one){
                if($max_win_money==0){$max_win_money = $one['win_money'];}         
                if($max_win_money==$one['win_money']){
                    $new_ar[] = ['uid'=>$one['uid'],'win'=>$one['win'],'win_money'=>$one['win_money']];   
                }else{
                    break;
                }                
            }
        }else{
            $new_ar = [];
        }
        return $new_ar;
    }


    /*周签到盈利最多的,有并列的*/
    public function find_max_earn_week($begin_day,$end_day){
        $data = self::$medoo->query("select uid,count(*) as win,sum(earn) as win_money from ".$this->title." where sign_ok=1 and is_end=1 and stage between  ".$begin_day." and ".$end_day." group by uid order by win_money desc")->fetchAll();
        if($data){
            $max_win_money = 0;
            foreach($data as $one){
                if($max_win_money==0){$max_win_money = $one['win_money'];}         
                if($max_win_money==$one['win_money']){
                    $new_ar[] = ['uid'=>$one['uid'],'win'=>$one['win'],'win_money'=>$one['win_money']];   
                }else{
                    break;
                }                
            }
        }else{
            $new_ar = [];
        }
        return $new_ar;
    }


    /*签到亏损最多的*/
    public function find_max_stake($stage){
        $data = self::$medoo->query("select uid,count(*) as win,sum(stake) as lost_money from ".$this->title." where stage like '%".$stage."%' and sign_ok=0 and is_end=1 group by uid order by lost_money desc")->fetchAll();
        if($data){
            $max_lost_money = 0;
            foreach($data as $one){
                if($max_lost_money==0){$max_lost_money = $one['lost_money'];}         
                if($max_lost_money==$one['lost_money']){
                    $new_ar[] = ['uid'=>$one['uid'],'win'=>$one['win'],'lost_money'=>$one['lost_money']];   
                }else{
                    break;
                }                
            }
        }else{
            $new_ar = [];
        }
        return $new_ar;
    }


    /*签到亏损最多的*/
    public function find_max_stake_week($begin_day,$end_day){
        $data = self::$medoo->query("select uid,count(*) as win,sum(stake) as lost_money from ".$this->title." where sign_ok=0 and is_end=1 and stage between  ".$begin_day." and ".$end_day." group by uid order by lost_money desc")->fetchAll();
        if($data){
            $max_lost_money = 0;
            foreach($data as $one){
                if($max_lost_money==0){$max_lost_money = $one['lost_money'];}         
                if($max_lost_money==$one['lost_money']){
                    $new_ar[] = ['uid'=>$one['uid'],'win'=>$one['win'],'lost_money'=>$one['lost_money']];   
                }else{
                    break;
                }                
            }
        }else{
            $new_ar = [];
        }
        return $new_ar;
    }


    
  
    
}
