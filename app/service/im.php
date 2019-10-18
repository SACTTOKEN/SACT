<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\service;
use app\model\user as UserModel; 
use app\model\admin as AdminModel; 

class im{
    public function url($url)
    {
        $account=cc('account','im');
        $random = getRandChar3(32); 
        $login_url = "https://console.tim.qq.com/v4/".$url."?usersig=".$account['usersig']."&identifier=".$account['identifier']."&sdkappid=".$account['appid']."&random=".$random."&contenttype=json";
        return $login_url;
    }
    

    //创建用户IM账号
    public function login_one($uid,$username,$nick="",$avatar=""){
        $login_url = $this->url("im_open_login_svc/account_import");
        
        $username=C("ptid")."_".$uid;
        $data['Identifier'] = $username;
        if($avatar!=""){
            $data['FaceUrl'] = $avatar;
        }
        if($nick!=""){
            $data['Nick'] = $nick;
        }
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);  
        $res = json_decode($res,true);
        if($res['ErrorCode']==0){
          $userM  = new UserModel();
          $ar['im']=$username;
          $userM->up($uid,$ar);
        }    
        return $res['ErrorCode'];     //错误码为0则导入成功
    }


    //是否在线 https://cloud.tencent.com/document/product/269/2566
    public function is_online($sid){
        $login_url = $this->url("openim/querystate");
        if(!($sid>0)){return 0;}
        $im_id = user_info($sid,'im');
        if(empty($im_id)){
            return 0;
        }
        //$im_id = c("ptid")."_".$sid; //yunpt
        $data['To_Account'] = [$im_id];
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);
        $res = json_decode($res,true);
        if($res['ErrorCode']==0){
            $my_res = $res['QueryResult'][0]['State'];   
            if($my_res=='Offline'){
                return 0;
            }else{
                return 1;
            }
        }
        return 0;  
    }
   

    //创建管理员IM账号
    public function login_admin($uid,$username,$nick="",$avatar=""){
        $login_url = $this->url("im_open_login_svc/account_import");
        $iden=c("ptid")."_admin_".$uid;
        $data['Identifier'] = $iden;
        if($avatar!=""){
            $data['FaceUrl'] = $avatar;
        }
        if($nick!=""){
            $data['Nick'] = $nick;
        }
        
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);  
        $res = json_decode($res,true);
        if($res['ErrorCode']==0){
            $adminM = new AdminModel();
            $ar['im']=$iden;
            $adminM->up($uid,$ar);
        }    
        return $res['ErrorCode'];     //错误码为0则导入成功
    }

    //修改IM资料
    public function edit_info($im,$nickname,$avatar,$sex)
    {
        $login_url = $this->url("profile/portrait_set");
        $data['From_Account'] = $im;
        $data['ProfileItem'][0]['tag'] = 'Tag_Profile_IM_Nick';
        $data['ProfileItem'][0]['Value'] = $nickname;
        $data['ProfileItem'][1]['tag'] = 'Tag_Profile_IM_Gender';
        if($sex==1){
        $sex="Gender_Type_Male";
        }elseif($sex==2){
        $sex="Gender_Type_Female";
        }else{
        $sex="Gender_Type_Unknown";
        }
        $data['ProfileItem'][1]['Value'] = $sex;
        $data['ProfileItem'][2]['tag'] = 'Tag_Profile_IM_Image';
        $data['ProfileItem'][2]['Value'] = $avatar;
      
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);  
        $res = json_decode($res,true);
       
    }

    //生成IM账号签名
    public function create_sig($im_name){
        $api = new \extend\im\TLSSigAPI();
        $sig = $api->genSig($im_name);//生成usersig
        return $sig;
    }

    //发红包
    public function redenvelope($content,$oid,$From_Account,$To_Account){
        $login_url = $this->url("openim/sendmsg");
      
        $data['SyncOtherMachine'] = 1;
        $data['From_Account'] = $From_Account;
        $data['To_Account'] = $To_Account;
        $data['MsgRandom'] = 1;
        $data['MsgTimeStamp'] = time();
        $data['MsgBody'] = [
            ['MsgType'=>'TIMCustomElem',
            'MsgContent'=>[
                'Data'=>$content,
                'Desc'=>$oid,
                'Ext'=>"2",
            ]]
        ];
        
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);

        $res = json_decode($res,true);
        if($res['ActionStatus']=='OK'){
            return true;
        }else{
            error($res['ErrorInfo'],400);
        } 
    }
    
    //创建群
    public function add_team($data)
    {
        $login_url = $this->url("group_open_http_svc/create_group");
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);
        $res = json_decode($res,true);
        if($res['ActionStatus']=='OK'){
            return true;
        }else{
            error($res['ErrorInfo'],400);
        } 
    }
    
    //修改群资料
    public function edit_team($data)
    {
        $login_url = $this->url("group_open_http_svc/modify_group_base_info");
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);
        $res = json_decode($res,true);
        if($res['ActionStatus']=='OK'){
            return true;
        }else{
            error($res['ErrorInfo'],400);
        } 
    }

    //加入群
    public function byjoining($data)
    {
        $login_url = $this->url("group_open_http_svc/add_group_member");
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);
        $res = json_decode($res,true);
        if($res['ActionStatus']=='OK'){
            return true;
        }else{
            error($res['ErrorInfo'],400);
        } 
    }


    //踢出群
    public function kick_out($data)
    {
        $login_url = $this->url("group_open_http_svc/delete_group_member");
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);
        $res = json_decode($res,true);
        if($res['ActionStatus']=='OK'){
            return true;
        }else{
            error($res['ErrorInfo'],400);
        } 
    }


    //转让群
    public function transfer($data)
    {
        $login_url = $this->url("group_open_http_svc/change_group_owner");
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);
        $res = json_decode($res,true);
        if($res['ActionStatus']=='OK'){
            return true;
        }else{
            error($res['ErrorInfo'],400);
        } 
    }


    //发消息
    public function message($data)
    {
        $login_url = $this->url("openim/sendmsg");
        $data_json = json_encode($data);
        $res = http_post($login_url,$data_json);
        $res = json_decode($res,true);
        if($res['ActionStatus']=='OK'){
            return true;
        }else{
            error($res['ErrorInfo'],400);
        } 
    }

}