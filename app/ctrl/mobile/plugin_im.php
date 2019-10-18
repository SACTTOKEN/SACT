<?php
/**
 * Created by yayue__god 
 * User: GOD 
 * Date: 2019-04-08 14:39:29
 * Desc: IM前台
 */


namespace app\ctrl\mobile;
use app\model\user as UserModel;

class plugin_im extends BaseController
{
	
    public $user_M;
    public function __initialize(){
        $this->user_M = new UserModel();
    }

    //直推
    public function zhituiren(){
       $uid = $GLOBALS['user']['uid']; 
       empty($uid) && error('请先登录');
       $tj = $this->user_M->find($GLOBALS['user']['tid'],['id','username','nickname','avatar','tel','rating','im','sex','created_time','vip_buy','mxq_buy']);
       
       $data = $this->user_M->find_son($uid);
      
       if($tj){
        array_unshift($data,$tj);
       }
       foreach($data as &$one){
            $user=user_info($one['id']);
            if($user['rating']==1 && $user['coin_rating']==1){
                $one['rating_cn'] = $user['rating_cn'];
                $one['coin_rating_cn'] = "";
            }elseif($user['rating']>1 && $user['coin_rating']==1){
                $one['rating_cn'] = $user['rating_cn'];
                $one['coin_rating_cn'] = "";
            }elseif($user['rating']=1 && $user['coin_rating']>1){
                $one['rating_cn'] = $user['coin_rating_cn'];
                $one['coin_rating_cn'] = "";
            }else{
                $one['rating_cn'] = $user['rating_cn'];
                $one['coin_rating_cn'] = $user['coin_rating_cn'];
            }
            $one['coin_yvip'] =$user['ynumber'];
            $one['vip_buy'] =$user['vip_buy'];
            $one['mxq_buy'] =$user['mxq_buy'];
            $one['rating_cn'] =$user['vip_rating_cn'];
       }
       return $data;
    }

    //客服
    public function service(){
       if(!plugin_is_open("btkfxt")){
           error('暂未开放',10007);
       }
       $user=$GLOBALS['user'];
       if(empty($user['im'])){
       $im = new \app\service\im();
       $im->login_one($user['id'],$user['username'],$user['nickname'],$user['avatar']);
       $data['im'] = C("ptid") . "_" . $user['id'];
       $data['im_sig'] = $im->create_sig($data['im']);
       }
       $where['service']=1;
       $where['role_id[!]']=1;
       $data['service']=(new \app\model\admin())->lists_all2($where,['nick_name','im']);
       return $data;
    }

    //欢迎语
    public function welcome()
    {
        $user=$GLOBALS['user'];
        if(!$user['im']){
            return;
        }
        (new \app\validate\ImTeamValidate())->goCheck('im');
        $im=post('im');
        $where['im']=$im;
        $where['service']=1;
        $where['show']=1;
        $to_user=(new \app\model\admin())->have($where);
        if(empty($to_user)){
            return;
        }
        if(empty($to_user['welcome'])){
            return;
        }
        
        $data['SyncOtherMachine'] = 1;
        $data['From_Account'] =$im;
        $data['To_Account'] =  $user['im'];
        $data['MsgRandom'] = 1;
        $data['MsgTimeStamp'] = time();
        $data['MsgBody'] = [
            ['MsgType'=>'TIMTextElem',
            'MsgContent'=>[
                'Text'=>$to_user['welcome'],
            ]]
        ];
        (new \app\service\im())->message($data);
        return "发送成功";
    }

    //机器人
    public function im_text()
    {
        $user=$GLOBALS['user'];
        if(!$user['im']){
            return;
        }
        (new \app\validate\ImTeamValidate())->goCheck('im');
        $im=post('im');
        $keyword=post('keyword');
        if(!$keyword){
            return;
        }

        $im_text_M=new \app\model\im_text();
        $where_text['keyword']=$keyword;
        $text_ar=$im_text_M->have($where_text);
        if(empty($text_ar)){
            $where_text2['keyword[~]']=$keyword;
            $where_text2['is_like']=1;
            $text_ar=$im_text_M->have($where_text2);
        }
        if(empty($text_ar)){
            return;
        }

        $where['im']=$im;
        $where['service']=1;
        $where['show']=1;
        $to_user=(new \app\model\admin())->have($where);
        if(empty($to_user)){
            return;
        }
        
        $data['SyncOtherMachine'] = 1;
        $data['From_Account'] = $im;
        $data['To_Account'] = $user['im'];
        $data['MsgRandom'] = 1;
        $data['MsgTimeStamp'] = time();
        $data['MsgBody'] = [
            ['MsgType'=>'TIMTextElem',
            'MsgContent'=>[
                'Text'=>$text_ar['content'],
            ]]
        ];
        (new \app\service\im())->message($data);
        return "发送成功";
    }

}