<?php
/**
 * Created by yayue__god 
 * User: GOD 
 * Date: 2019-04-08 14:39:29
 * Desc: IM前台
 */


namespace app\ctrl\mobile;
use app\model\user as UserModel;

class im_team extends BaseController
{
	
    public $user_M;
    public $im_team_M;
    public function __initialize(){
        if(!plugin_is_open("ltskt")){
            error('没有权限',10007);
        }
        $this->user_M = new UserModel();
        $this->im_team_M=new \app\model\im_team();
    }

    
    //创建战队
    public function index()
    {
        $user=$GLOBALS['user'];
        if(!empty($user['im_team'])){
            error('已加入团队',404);
        }
        $data['bteam']['title']="初级战队";
        $data['bteam']['zt_num']=c("bteam_zt_num");
        $data['bteam']['dd_num']=c("bteam_dd_num");
        $data['bteam']['create']=c("bteam_charge")/10;
        $data['bteam']['money']=c("bteam_pay_money");
        $data['bteam']['money_cn']=find_reward_redis(c("bteam_pay_create"));
        if(c("team_right_join")==1){
            if(strstr(c("bteam_join_condition"),"直推会员人数")){
                $data['bteam']['is_zt']=1;
                $data['bteam']['zt']=$this->im_team_M->zt();
            }else{
                $data['bteam']['is_zt']=0;
            }
            if(strstr(c("bteam_join_condition"),"团队会员人数")){
                $data['bteam']['is_dd']=1;
                $data['bteam']['dd']=$this->im_team_M->dd();
            }else{
                $data['bteam']['is_dd']=0;
            }
        }else{
            $data['bteam']['is_zt']=0;
            $data['bteam']['is_dd']=0;
        }
        $condition=1;
        $rating=c('bteam_rating');
        if($rating=="商城等级"){
            if($user['yvip']<c("bteam_zt_num")){
                $condition=0;
            }
            if($user['zvip']<c("bteam_dd_num")){
                $condition=0;
            }
        }else{
            if($user['coin_yvip']<c("bteam_zt_num")){
                $condition=0;
            }
            if($user['coin_zvip']<c("bteam_dd_num")){
                $condition=0;
            }
        }
        $data['bteam']['condition']=$condition;
       
        
        $data['hteam']['title']="高级战队";
        $data['hteam']['zt_num']=c("hteam_zt_num");
        $data['hteam']['dd_num']=c("hteam_dd_num");
        $data['hteam']['create']=c("hteam_charge")/10;
        $data['hteam']['money']=c("hteam_pay_money");
        $data['hteam']['money_cn']=find_reward_redis(c("hteam_pay_create"));
        if(c("team_right_join")==1){
            if(strstr(c("hteam_join_condition"),"直推会员人数")){
                $data['hteam']['is_zt']=1;
                $data['hteam']['zt']=$this->im_team_M->zt();
            }else{
                $data['hteam']['is_zt']=0;
            }
            if(strstr(c("hteam_join_condition"),"团队会员人数")){
                $data['hteam']['is_dd']=1;
                $data['hteam']['dd']=$this->im_team_M->dd();
            }else{
                $data['hteam']['is_dd']=0;
            }
        }else{
            $data['bteam']['is_zt']=0;
            $data['bteam']['is_dd']=0;
        }
        $condition=1;
        $rating=c('hteam_rating');
        if($rating=="商城等级"){
            if($user['yvip']<c("hteam_zt_num")){
                $condition=0;
            }
            if($user['zvip']<c("hteam_dd_num")){
                $condition=0;
            }
        }else{
            if($user['coin_yvip']<c("hteam_zt_num")){
                $condition=0;
            }
            if($user['coin_zvip']<c("hteam_dd_num")){
                $condition=0;
            }
        }
        $data['hteam']['condition']=$condition;
        return $data;
    }


    //添加创建战队
    public function add_team()
    {
        (new \app\validate\ImTeamValidate())->goCheck('add_create_team');
        $user=$GLOBALS['user'];
        if(!empty($user['im_team'])){
            error('已加入团队',400);
        }
        $cate=post('cate');
        $title=post('title');
        $avatar=post('avatar');
        $slogan=post('slogan');
        $zt=post('zt');
        $dd=post('dd');
        if($cate=="初级战队"){
            $rating=c('bteam_rating');
            if($rating=="商城等级"){
                if($user['yvip']<c("bteam_zt_num")){
                    error('直推会员人数不足',400);
                }
                if($user['zvip']<c("bteam_dd_num")){
                    error('团队人数不足',400);
                }
            }else{
                if($user['coin_yvip']<c("bteam_zt_num")){
                    error('直推会员人数不足',400);
                }
                if($user['coin_zvip']<c("bteam_dd_num")){
                    error('团队人数不足',400);
                }
            }

            if(strstr(c("bteam_join_condition"),"直推会员人数")){
                if(c("team_right_join")==1){
                    if($zt==''){
                        error('请选择直推人数条件',400);
                    }
                }else{
                    $zt=c("bteam_default_zt");
                }
            }else{
                $zt=0;
            }
            if(strstr(c("bteam_join_condition"),"团队会员人数")){
                if(c("team_right_join")==1){
                    if($dd==''){
                        error('请选择团队人数条件',400);
                    }
                }else{
                    $dd=c("bteam_default_dd");
                }
            }else{
                $dd=0;
            }
            
            $money=c("bteam_pay_money");
            $create=c("bteam_pay_create");
            $cate=0;
        }elseif($cate=="高级战队"){
            $rating=c('hteam_rating');
            if($rating=="商城等级"){
                if($user['yvip']<c("hteam_zt_num")){
                    error('直推会员人数不足',400);
                }
                if($user['zvip']<c("hteam_dd_num")){
                    error('团队人数不足',400);
                }
            }else{
                if($user['coin_yvip']<c("hteam_zt_num")){
                    error('直推会员人数不足',400);
                }
                if($user['coin_zvip']<c("hteam_dd_num")){
                    error('团队人数不足',400);
                }
            }

            if(strstr(c("hteam_join_condition"),"直推会员人数")){
                if(c("team_right_join")==1){
                    if($zt==''){
                        error('请选择直推人数条件',400);
                    }
                }else{
                    $zt=c("hteam_default_zt");
                }
            }else{
                $zt=0;
            }
            if(strstr(c("hteam_join_condition"),"团队会员人数")){
                if(c("team_right_join")==1){
                    if($dd==''){
                        error('请选择团队人数条件',400);
                    }
                }else{
                    $dd=c("hteam_default_dd");
                }
            }else{
                $dd=0;
            }

            $money=c("hteam_pay_money");
            $create=c("hteam_pay_create");
            $cate=1;
        }else{
            error('请选择战队类型',400);
        }
        $ptid=c('ptid');
        
        flash_god($user['id']);
        //开始
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        //判断金额
        $user_M = new \app\model\user();
        $ar = $user_M->find($user['uid'],$create);
        if($money-$ar>0){
            error('金额不足',10003);
        }
        $data['boss_uid']=$user['id'];
        $data['team_num']=1;
        $data['cate']=$cate;
        $data['title']=$title;
        $data['avatar']=$avatar;
        $data['slogan']=$slogan;
        $data['zt']=$zt;
        $data['dd']=$dd;
        $res=$this->im_team_M->save($data);
        empty($res) && error('添加失败',10006);	
        $team_ar['im']=$ptid.'_team_'.$res;
        $this->im_team_M->up($res,$team_ar);

        $money_S = new \app\service\money();
        if($money>0){
        $money_res = $money_S->minus($user['uid'],$money,$create,'im_team',$res,$user['uid'],'创建战队'); //记录资金流水
        empty($money_res) && error('添加失败1',10005);  
        }

        //im
        $team['Owner_Account'] = $user['im'];
        $team['Type'] = "Public";
        $team['GroupId'] = $team_ar['im'];
        $team['Name'] = $data['title'];
        $team['Introduction'] = $data['slogan'];
        $team['FaceUrl'] = $data['avatar'];
        $team['ApplyJoinOption'] = 'NeedPermission';
        (new \app\service\im())->add_team($team);
        $this->user_M->up($user['id'],['im_team'=>$res,'is_im_team'=>1]);
        //结束
        $Model->run();
        $redis->exec();

        return "创建成功";
    }


    //战队列表 
    public function lists()
    {
        $user=$GLOBALS['user'];
        if(!empty($user['im_team'])){
            if($user['is_im_team']==0){
                $data['types']=1;
            }else{
                $data['types']=2;
            }
            $res['data']=[$this->im_team_M->find($user['im_team'])];
            if($res['data']['zt']!='0' || $res['data']['dd']!='0'){
                $res['data'][0]['is_open_cn']="条件加入";
            }elseif($res['data'][0]['is_open']==0){
                $res['data'][0]['is_open_cn']="审核加入";
            }else{
                $res['data'][0]['is_open_cn']="开放加入";
            }
            $data['lists']=$res;
        }else{
            (new \app\validate\PageValidate())->goCheck();
            (new \app\validate\AllsearchValidate())->goCheck();
            $title=post("title");
            if($title){
                $where['im'] = $title;
            }
            $where['is_show'] = 1;
            $page=post("page",1);
            $page_size = post("page_size",10);		
            $team=$this->im_team_M->lists($page,$page_size,$where);
            foreach($team as &$vo){
                if($vo['zt']!='0' or $vo['dd']!='0'){
                    $vo['is_open_cn']="条件加入";
                }elseif($vo['is_open']==0){
                    $vo['is_open_cn']="审核加入";
                }else{
                    $vo['is_open_cn']="开放加入";
                }
                if($vo['cate']==0){
                    $people=c("bteam_man_limit");
                }else{
                    $people=c("hteam_man_limit");
                }
                if($vo['team_num']>=$people){
                    $vo['is_join']=1;
                }else{
                    $vo['is_join']=0;
                }
            }
           
            $res['data'] = $team; 

            $data['types']=3;
            $data['lists']=$res;
        }
        return $data;
    }

    //申请加入
    public function join()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $user=$GLOBALS['user'];
        if(!empty($user['im_team'])){
            error('已加入团队',400);
        }
        $id=post("id");
        $data=$this->im_team_M->find($id);
        empty($data) && error('数据不存在',404);	
        
        if($data['cate']==0){
            $join_condition=c("bteam_join_condition");
            $rating=c("bteam_rating");
            $people=c("bteam_man_limit");
        }else{
            $join_condition=c("hteam_join_condition");
            $rating=c("hteam_rating");
            $people=c("hteam_man_limit");
        }
        if($user['im_team_time']+c('team_out_time')*60*60>time()){
            error(c('team_out_time').'小时后才能申请加入战队',400);
        }
        //判断条件
        if($rating=="商城等级"){
            $zt_num=$user['yvip'];
            $dd_num=$user['zvip'];
        }else{
            $zt_num=$user['coin_yvip'];
            $dd_num=$user['coin_zvip'];
        }

        

        if($data['zt']>0 and strstr($join_condition,"直推会员人数")){
            $zt=$this->im_team_M->zt();
            if($zt[$data['zt']]>$zt_num){
                error('直推人数条件不足',400);
            }
        }
        if($data['dd']>0 and strstr($join_condition,"团队会员人数")){
            $dd=$this->im_team_M->dd();
            if($dd[$data['dd']]>$dd_num){
                error('团队人数条件不足',400);
            }
        }
        //人数限制
        if($data['team_num']>=$people){
            error('团队人数已满',400);
        }

        //加入
        if($data['is_open']==0){
            $this->user_M->up($user['id'],['im_team'=>$data['id'],'is_im_team'=>0]);	
            $this->im_team_M->up($data['id'],['team_num[+]'=>1]);
        }else{
            $team['GroupId']=$data['im'];
            $team['MemberList']=[
                ['Member_Account'=>$user['im']],
            ];
            (new \app\service\im())->byjoining($team);
            $this->user_M->up($user['id'],['im_team'=>$data['id'],'is_im_team'=>1]);
            $this->im_team_M->up($data['id'],['team_num[+]'=>1]);
        }
        return "申请成功";
    }

    //取消申请
    public function del_join()
    {
        $user=$GLOBALS['user'];
        if(empty($user['im_team'])){
            error('未申请加入团队',400);
        }
        if($user['is_im_team']==1){
            error('申请加入团队已通过',400);
        }
        $this->user_M->up($user['id'],['im_team'=>'','is_im_team'=>0]);
        $this->im_team_M->up($user['im_team'],['team_num[-]'=>1]);
        return "取消成功";
    }

    //战队管理
    public function edit()
    {
        (new \app\validate\ImTeamValidate())->goCheck('im');
        $user=$GLOBALS['user'];
        $im=post("im");
        $where['im']=$im;
        $data=$this->im_team_M->have($where);
        empty($data) && error('数据不存在',404);
        if($data['id']!=$user['im_team']){
            error('你不是群用户',10007);
        }

        $user_ar=$this->user_M->lists_all(['im_team'=>$data['id']],['id','username','nickname','avatar','coin_rating','is_im_team']);
        if($data['cate']==0){
            $rating=c("bteam_rating");
        }else{
            $rating=c("hteam_rating");
        }
        foreach($user_ar as &$vo){
            $users=user_info($vo['id']);
            if($rating=="商城等级"){
                $vo['team_num']=$users['zvip'];
            }else{
                $vo['team_num']=$users['coin_zvip'];
            }
            $vo['coin_rating_cn']=$users['coin_rating_cn'];
            $vo['nickname']=$vo['nickname'] ? $vo['nickname'] : $vo['username'];
        }
        $data['team']=$user_ar;
        
        $data['lord']=0;
        if($data['boss_uid']==$user['id']){
            $data['lord']=1;
            $data['config']['team_right_join']=c("team_right_join");
            $data['config']['team_right_stoptalk']=c("team_right_stoptalk");
            $data['config']['team_right_kick']=c("team_right_kick");
            $data['config']['team_right_transfer']=c("team_right_transfer");
            $data['config']['team_right_prohibited']=c("team_right_prohibited");
            if($data['config']['team_right_join']==1){
                if($data['cate']==0){
                if(strstr(c("bteam_join_condition"),"直推会员人数")){
                        $data['config']['is_zt']=1;
                        $data['config']['zt']=$this->im_team_M->zt();
                    }else{
                        $data['config']['is_zt']=0;
                    }
                    if(strstr(c("bteam_join_condition"),"团队会员人数")){
                        $data['config']['is_dd']=1;
                        $data['config']['dd']=$this->im_team_M->dd();
                    }else{
                        $data['config']['is_dd']=0;
                    }
                }else{   
                    if(strstr(c("hteam_join_condition"),"直推会员人数")){
                        $data['config']['is_zt']=1;
                        $data['config']['zt']=$this->im_team_M->zt();
                    }else{
                        $data['config']['is_zt']=0;
                    }
                    if(strstr(c("hteam_join_condition"),"团队会员人数")){
                        $data['config']['is_dd']=1;
                        $data['config']['dd']=$this->im_team_M->dd();
                    }else{
                        $data['config']['is_dd']=0;
                    }
                }
            }
        }
        return $data;
    }

    //修改管理
    public function saveedit()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $user=$GLOBALS['user'];
        $id=post("id");
        $data=$this->im_team_M->find($id);
        empty($data) && error('数据不存在',404);
        if($data['boss_uid']!=$user['id']){
            error('你不是群主',10007);
        }

        (new \app\validate\ImTeamValidate())->goCheck('edit_create_team');
        $title=post('title');
        $avatar=post('avatar');
        $slogan=post('slogan');
        $zt=post('zt');
        $dd=post('dd');
        $is_open=post('is_open');
        $is_chat=post('is_chat');
        if($data['cate']==0){
            if(strstr(c("bteam_join_condition"),"直推会员人数")){
                if(c("team_right_join")==1){
                    if($zt==''){
                        error('请选择直推人数条件',400);
                    }
                }else{
                    $zt=c("bteam_default_zt");
                }
            }else{
                $zt=0;
            }
            if(strstr(c("bteam_join_condition"),"团队会员人数")){
                if(c("team_right_join")==1){
                    if($dd==''){
                        error('请选择团队人数条件',400);
                    }
                }else{
                    $dd=c("bteam_default_dd");
                }
            }else{
                $dd=0;
            }
            
        }else{
            if(strstr(c("hteam_join_condition"),"直推会员人数")){
                if(c("team_right_join")==1){
                    if($zt==''){
                        error('请选择直推人数条件',400);
                    }
                }else{
                    $zt=c("hteam_default_zt");
                }
            }else{
                $zt=0;
            }
            if(strstr(c("hteam_join_condition"),"团队会员人数")){
                if(c("team_right_join")==1){
                    if($dd==''){
                        error('请选择团队人数条件',400);
                    }
                }else{
                    $dd=c("hteam_default_dd");
                }
            }else{
                $dd=0;
            }

        }

        //更新
        $data['title']=$title;
        $data['avatar']=$avatar;
        $data['slogan']=$slogan;
        $data['is_open']=$is_open;
        if(c('team_right_stoptalk')==1){
            if($is_chat==='0' || $is_chat==='1'){
                $data['is_chat']=$is_chat;
            }else{
                error('是否全员禁言',400);
            }
        }else{
            $data['is_chat']=1;
        }
        $data['zt']=$zt;
        $data['dd']=$dd;

        //im
        $team['GroupId'] = $data['im'];
        $team['Name'] = $data['title'];
        $team['Introduction'] = $data['slogan'];
        $team['FaceUrl'] = $data['avatar'];
        $team['ApplyJoinOption'] = 'NeedPermission';
        if($data['is_chat']==1){
            $team['ShutUpAllMember'] = 'Off';
        }else{
            $team['ShutUpAllMember'] = 'On'; 
        }
        (new \app\service\im())->edit_team($team);

        $this->im_team_M->up($data['id'],$data);
        return "修改成功";
    }


    //审核加入
    public function byjoining()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        (new \app\validate\ImTeamValidate())->goCheck('other_id');
        $id=post("id");
        $data=$this->im_team_M->find($id);
        empty($data) && error('数据不存在',404);
        if($data['boss_uid']!=$GLOBALS['user']['id']){
            error('你不是群主',404);
        }
        $uid=post("other_id");
        $user=$this->user_M->find($uid,['id','im','im_team','is_im_team']);
        if($user['im_team']!=$data['id']){
            error('未申请加入',404);
        }
        if($user['is_im_team']!=0){
            error('已加入群',404);
        }
        
        $team['GroupId']=$data['im'];
        $team['MemberList']=[
            ['Member_Account'=>$user['im']],
        ];
        (new \app\service\im())->byjoining($team);
        $this->user_M->up($uid,['is_im_team'=>1]);
        return "审核成功";
    }

    //审核拒绝
    public function refuse()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        (new \app\validate\ImTeamValidate())->goCheck('other_id');
        $id=post("id");
        $data=$this->im_team_M->find($id);
        empty($data) && error('数据不存在',404);
        if($data['boss_uid']!=$GLOBALS['user']['id']){
            error('你不是群主',404);
        }
        $uid=post("other_id");
       
        $this->user_M->up($uid,['im_team'=>'','is_im_team'=>0]);
        return "拒绝成功";
    }


    //踢出
    public function kick_out()
    {
        if(c('team_right_kick')==0){
            error('无法踢出用户',404);
        }
        (new \app\validate\IDMustBeRequire())->goCheck();
        (new \app\validate\ImTeamValidate())->goCheck('other_id');
        $id=post("id");
        $data=$this->im_team_M->find($id);
        empty($data) && error('数据不存在',404);
        if($data['boss_uid']!=$GLOBALS['user']['id']){
            error('你不是群主',404);
        }
        
        $uid=post("other_id");
        if($data['boss_uid']==$uid){
            error('你是群主',404);
        }
        $user=$this->user_M->find($uid,['id','im','im_team','is_im_team']);
        if($user['im_team']!=$data['id']){
            error('未申请加入',404);
        }
        if($user['is_im_team']==0){
            error('未审核通过',404);
        }
        
        $team['GroupId']=$data['im'];
        $team['MemberToDel_Account']=[
            $user['im'],
        ];
        (new \app\service\im())->kick_out($team);
        $this->user_M->up($uid,['im_team'=>'','is_im_team'=>0]);
        $this->im_team_M->up($data['id'],['team_num[-]'=>1]);
        return "踢出成功";
    }

    //退出
    public function drop_out()
    {
        $user=$GLOBALS['user'];
        $data=$this->im_team_M->find($user['im_team']);
        empty($data) && error('数据不存在',404);
       
        if($user['is_im_team']==0){
            error('未审核通过',404);
        }
        
        $team['GroupId']=$data['im'];
        $team['MemberToDel_Account']=[
            $user['im'],
        ];
        (new \app\service\im())->kick_out($team);
        $this->user_M->up($user['id'],['im_team'=>'','is_im_team'=>0,'im_team_time'=>time()]);
        $this->im_team_M->up($data['id'],['team_num[-]'=>1]);
        return "退出成功";
    }

    //转让群
    public function transfer()
    {
        if(c('team_right_transfer')==0){
            error('无法转让群',404);
        }
        (new \app\validate\IDMustBeRequire())->goCheck();
        (new \app\validate\ImTeamValidate())->goCheck('other_id');
        $id=post("id");
        $data=$this->im_team_M->find($id);
        empty($data) && error('数据不存在',404);
        if($data['boss_uid']!=$GLOBALS['user']['id']){
            error('你不是群主',404);
        }
        $uid=post("other_id");
        if($data['boss_uid']==$uid){
            error('你是群主',404);
        }
        $user=user_info($uid);
        if($user['im_team']!=$data['id']){
            error('未申请加入',404);
        }
        if($user['is_im_team']==0){
            error('未加入群',404);
        }
        

        if($data['cate']=="0"){
            $rating=c('bteam_rating');
            if($rating=="商城等级"){
                if($user['yvip']<c("bteam_zt_num")){
                    error('直推会员人数不足',400);
                }
                if($user['zvip']<c("bteam_dd_num")){
                    error('团队人数不足',400);
                }
            }else{
                if($user['coin_yvip']<c("bteam_zt_num")){
                    error('直推会员人数不足',400);
                }
                if($user['coin_zvip']<c("bteam_dd_num")){
                    error('团队人数不足',400);
                }
            }
        }else{
            $rating=c('hteam_rating');
            if($rating=="商城等级"){
                if($user['yvip']<c("hteam_zt_num")){
                    error('直推会员人数不足',400);
                }
                if($user['zvip']<c("hteam_dd_num")){
                    error('团队人数不足',400);
                }
            }else{
                if($user['coin_yvip']<c("hteam_zt_num")){
                    error('直推会员人数不足',400);
                }
                if($user['coin_zvip']<c("hteam_dd_num")){
                    error('团队人数不足',400);
                }
            }

        }

        $team['GroupId']=$data['im'];
        $team['NewOwner_Account']=$user['im'];
        (new \app\service\im())->transfer($team);
        $this->im_team_M->up($data['id'],['boss_uid'=>$user['id']]);
        return "转移成功";
    }


    public function recommended()
    {
        $user=$GLOBALS['user'];
        if($user['im_team']=='' || $user['is_im_team']!=1){
            error('您还未加入战队',404);
        }
        (new \app\validate\ImTeamValidate())->goCheck('im');
        $im=post('im');
        $where['im']=$im;
        $where['im_team[>]']=0;
        $to_user=$this->user_M->is_have($where);
        !empty($to_user) && error("对方已加入团队",404);
        
        $data['SyncOtherMachine'] = 1;
        $data['From_Account'] = $user['im'];
        $data['To_Account'] = $im;
        $data['MsgRandom'] = 1;
        $data['MsgTimeStamp'] = time();
        $data['MsgBody'] = [
            ['MsgType'=>'TIMCustomElem',
            'MsgContent'=>[
                'Data'=>'推荐战队',
                'Desc'=>(new \app\model\im_team())->find($user['im_team'],'im'),
                'Ext'=>"3",
            ]]
        ];

        (new \app\service\im())->message($data);
        return "推荐成功";
    }
    
}