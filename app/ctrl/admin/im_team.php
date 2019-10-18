<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-27 14:43:27
 * Desc: IM战队
 */
namespace app\ctrl\admin;
use app\ctrl\admin\BaseController;
use app\model\im_team as ImTeamModel;
use app\model\user as UserModel;
use app\validate\IDMustBeRequire;
use app\validate\ImTeamValidate;

class im_team extends BaseController{

    public $im_team_M;
    public $userM;
    public function __initialize(){
        $this->im_team_M = new ImTeamModel();
        $this->userM = new UserModel();
    }

    /*战队列表*/
    public function lists()
    {
        $where = [];

        $username = post('username','');
        $nickname = post('nickname','');
        $is_show  = post('is_show','');

        if($username){
            $where['boss_uid'] = $this->userM->find_mf_uid($username);
        }
        if($nickname){
            $where['boss_uid'] = $this->userM->find_mf_uid_plus($nickname);
        }
        if(is_numeric($is_show)){
            $where['is_show'] = $is_show;
        }

        $page=post("page",1);
        $page_size = post("page_size",10);
        $data=$this->im_team_M->lists($page,$page_size,$where);
        foreach($data as &$one){
            $boss = user_info($one['boss_uid']);
            $one['boss_avatar'] = $boss['avatar'];
            $one['boss_username'] = $boss['username'];
            $one['boss_nickname'] = $boss['nickname'];
        }
        $count = $this->im_team_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res;         
    }

    /*查看战队人员*/
    public function team_people(){
        (new IDMustBeRequire())->goCheck();        
        $where = [];
        $team_id = post("id");
        if($team_id>0){
            $where['im_team'] = $team_id;
        }   
        $page=post("page",1);
        $page_size = post("page_size",10);      
        $data=$this->userM->lists($page,$page_size,$where);
        // var_dump($this->userM->log());
        // exit();

        $reward_M = new \app\model\reward();
        $reward_ar = $reward_M->title_by_types(2); //types为2的奖励组

        $coin_rating_M = new \app\model\coin_rating();

        foreach($data as &$rs){
            $rs['tid_cn']  = user_info($rs['tid'],'username');
            if($rs['coin_rating']){
                $rs['coin_rating_cn'] = $coin_rating_M->find($rs['coin_rating'],'title'); //矿机等级中文 和商城等级不同，只要等级中文和ID。
            }
            
            foreach($reward_ar as &$one){
                $one['value'] = $rs[$one['iden']] ? $rs[$one['iden']] : '0.000000';
            }
            $rs['reward_ar'] = $reward_ar;
            unset($one);
        }
        unset($rs);

        $count = $this->userM->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }

    /*冻结战队 0否 1是 默认0未冻结*/
    public function team_lock(){
        (new IDMustBeRequire())->goCheck();        
        $team_id = post('id');
        $check = post('is_show',0);
        $data['is_show'] = $check;   
        $res = $this->im_team_M->up($team_id,$data);


        empty($res) && error('操作失败',404);
        return $res;
    }


    /*单条记录*/
    public function detail(){
        (new IDMustBeRequire())->goCheck();
        $team_id = post('id');
        $res = $this->im_team_M->find($team_id);
        if($res['boss_uid']){
            $info = user_info($res['boss_uid']);
            $res['username'] = $info['username'];    //$this->userM->find($res['boss_uid'], 'username');  
            $res['nickname'] = $info['nickname'];
        }  
        return $res;
    }


    /*更换队长*/
    public function change_captain(){
        (new IDMustBeRequire())->goCheck();   
        $team_id = post('id');
        $username = post('username','');
        $uid = $this->userM->find_uid($username);
        empty($uid) && error('该会员不存在');

        $data=$this->im_team_M->find($team_id);
        empty($data) && error('数据不存在',404);
        
        $user=user_info($uid);
        if($user['im_team']!=$data['id']){
            error('该会员不是本群群员',404);
        }
        if($user['is_im_team']==0){
            error('该会员不是本群群员',404);
        }
        if($user['id']==$data['boss_uid']){
            error('该会员已经是本群群主',404);
        }
        
        $team['GroupId']=$data['im'];
        $team['NewOwner_Account']=$user['im'];
        (new \app\service\im())->transfer($team);
        $this->im_team_M->up($data['id'],['boss_uid'=>$user['id']]);
        return "转移成功";
    }


}