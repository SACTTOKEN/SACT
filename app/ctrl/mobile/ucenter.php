<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 17:02:46
 * Desc: 会员中心控
 */
namespace app\ctrl\mobile;
use app\model\user as UserModel;
use app\ctrl\mobile\BaseController;

class ucenter extends BaseController
{
	public $user_M;
	
	public function __initialize(){
		$this->user_M = new UserModel();
        if(isset($GLOBALS['user']['id'])){
            $uid = $GLOBALS['user']['id']; 
        }
        empty($uid) && error('请登录',606);
	}


	/*编辑资料*/
	public function edit_userinfo(){
        (new \app\validate\UcenterValidate())->goCheck('scene_edit_userinfo');
        $uid = $GLOBALS['user']['id']; 
        $data = post(['nickname','province','city','area','town','avatar']);
		$res = $this->user_M->up($uid,$data);
        empty($res) && error('修改失败',400);
        return $res;
    }
    
    
    //绑定推广码
    public function bind_share()
    {
        $uid=$GLOBALS['user']['id'];
        $tshare=post('tshare');
        $tid=$this->user_M->find($uid,'tid');
        if(!empty($tid)){
            error('已绑定推荐人',404);
        }
        if ($tshare) {
            $tid = $this->user_M->find_by_share($tshare);
            empty($tid) && error('推荐人不存在', 400);
            if($tid==$uid){
                error('推荐人不能是自己', 400);
            }

            $user_S = new \app\service\user();
    		$judge_res = $user_S->judge_tid($uid,$tid);
            empty($judge_res) && error('该推荐人是你的下级会员！',404);
            
            $data['tid'] = $tid;
        }else{
            empty($tid) && error('推荐人不存在', 400);
        }
      
        flash_god($uid);
     	$redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();
      
        $new_uid = $this->user_M->up($uid,$data);
        empty($new_uid) && error('提交失败',10008);
        $users = new \app\service\user();
        $users->reg_run($uid);
        
        $Model->run();
        $redis->exec();

        mb_sms('recommend_user_reg',$uid);
      
        return '绑定成功';
    }


    /*绑定手机号，只有一种情况，关注公众号生成的账号需要绑定手机号*/
    /*判断是否已存在该手机号,存在则把公众号生成的账号openid,昵称,头像移到已存在手机号所在账号，并删除公众号生成的账号*/
    public function bind_mobile(){
        //验证code BEGIN
        $tel = post('tel','');
        $unicode = post("unicode");  
        $code = post('code',''); 
        $vue_code = $code."@".$unicode;     
        $redis = new \core\lib\redis();
        $redis_code = $redis -> get("sms:".$tel);
        if($vue_code != $redis_code){ //$code."@".uniqid();
        error('验证码错误',400);
        }
        $redis->set("sms:".$tel,'');
        //验证code END
        
        if(isset($GLOBALS['user']['tel'])  && !empty($GLOBALS['user']['tel'])){
            error('你已绑定手机号',404);
        }
        $uid = $GLOBALS['user']['id'];  
        $where['tel'] = $tel;
        $web = $this->user_M->have($where);
        if(isset($web['openid']) && $web['openid']!=''){
            error('该手机号已绑定微信号，请更换绑定手机号',404);
        }
        if($web){

            $gzh = $this->user_M->find($uid);
            $data['nickname'] = $gzh['nickname'];
            $data['avatar'] = $gzh['avatar'];
            $data['openid'] = $gzh['openid'];
            $this->user_M ->up($web['id'],$data);
            (new \app\model\order())->up_all(['uid'=>$uid],['uid'=>$web['id']]);
            $this->user_M ->del($uid);
            
            //强行登陆
            $users = new \app\service\user();
            $users -> logins_run($web['id']);
            $token=new \app\service\token();             
            $res = $token->usertoken($web['id']);
            set_cookie("user",'{"uid":"'.$web['id'].'","user_key":"'.$res['user_key'].'"}'); 

        }else{
            $data['tel'] = $tel;
            $res = $this->user_M->up($uid,$data);
        }
        return true;
    }


    /*发送手机验证码*/
    public function sendcode(){
        $tel = post("tel");
        $quhao = post("quhao");
        $quhao = $quhao ? $quhao : '86';
        if(isset($GLOBALS['user']['tel']) && !empty($GLOBALS['user']['tel'])){
            error('你已绑定手机号',404);
        }
        $where['tel'] = $tel;
        $web = $this->user_M->have($where);
        if($web){
            if(isset($web['openid']) && $web['openid']!=''){
                error('该手机号已绑定微信号，请更换绑定手机号',404);
            }
        }

        $msms_C = new \app\service\msms();
        $res = $msms_C->send($tel,$quhao);
        if($res['status']==0){
            error($res['info'],404);
        }
        return $res['info'];
    }


    /*修改密码*/
    public function change_password(){
        (new \app\validate\UcenterValidate())->goCheck('scene_change_password');
        $uid = $GLOBALS['user']['id'];
       
        $tel = post('tel');
        //验证手机begin
        
        $my_tel = $this->user_M->find($uid,'tel');
        if($my_tel!=$tel){
            error('手机号错误',400);
        }    

        $is_yzm = plugin_is_open('btdx');
        $is_gjyzm = plugin_is_open('gjdx');
        if(($is_yzm==1 || $is_gjyzm==1) && c('xgmmdxyz')==1){
            $unicode = post("unicode");  
            $code = post('code',''); 
            $vue_code = $code."@".$unicode;  
            $redis = new \core\lib\redis();
            $redis_code = $redis -> get("sms:".$tel);
            if($vue_code != $redis_code){  //$code."@".uniqid();
            error('验证码错误！',400);
            }
            $redis->set("sms:".$tel,'');
        }


        //验证手机end
        $new_password = post('new_password');
        $new_password = rsa_decrypt($new_password);
        $new_password = md5($new_password.'inex10086');
        $res = $this->user_M->change_password($uid,$new_password);
        empty($res) && error('修改失败',400);
        return $res;
    }


    /*支付密码*/
    public function pay_password(){
        $uid = $GLOBALS['user']['id'];
        $code = post('code');
        $tel = post('tel');
        //验证手机begin
        $my_tel = $this->user_M->find($uid,'tel');
        if($my_tel!=$tel){
            error('手机号错误',400);
        }   
        $is_yzm = plugin_is_open('btdx');
        $is_gjyzm = plugin_is_open('gjdx');
        if(($is_yzm==1 || $is_gjyzm==1) && c('zfmmdxyz')==1){
            $unicode = post("unicode");  
            $code = post('code',''); 
            $vue_code = $code."@".$unicode;  
            $redis = new \core\lib\redis();
            $redis_code = $redis -> get("sms:".$tel);
            if($vue_code != $redis_code){  //$code."@".uniqid();
            error('验证码错误！',400);
            }
            $redis->set("sms:".$tel,'');
        }   
        //验证手机end


        $pay_password = post('pay_password');
        $pay_password = rsa_decrypt($pay_password);
        $pay_password = md5($pay_password.'inex10086');

        $user_M = new \app\model\user();
        $res = $user_M->change_pay_password($uid,$pay_password);
        empty($res) && error('设置失败',400);
        return $res;
    }


    /*站内信*/        
    public function my_letter(){
        $uid = $GLOBALS['user']['id'];
        $letter_M = new \app\model\user_letter();
        $data = $letter_M->lists_all($uid);
        return $data;
    }


    /*资金流水*/
    public function my_money(){
        $uid = $GLOBALS['user']['id'];
        $money_M = new \app\model\money();
        $data = $money_M->lists($uid);
        return $data;
    }


    /*分享链接*/
    public function my_share_link(){
        $uid = $GLOBALS['user']['id'];
        $username = $GLOBALS['user']['username'];
        $front_link = post('front_link');
        empty($res) && error('请传参数front_link',400);
        $web_name = CC('web_config','api');   
        //http://show3.cn1218.com/phone/zhuce.asp?tgid=18650071918
        $link = "http://".$web_name."/".$front_link."?tgid=".$username;
        return $link;
    } 
    
    /*二维码*/
    public function my_QR(){
        $uid = $GLOBALS['user']['id'];
        $data['wx_rem'] = $GLOBALS['user']['wx_ewm'];
        $data['wy_ewm'] = $GLOBALS['user']['wy_ewm'];
        return $data;
    }


    /*身份证认证*/
    public function sfrz(){
        (new \app\validate\UcenterValidate())->goCheck('scene_sfrz'); 
        $uid = $GLOBALS['user']['id'];
        //身份证号码
        $params['cardNo']=post('cardNo');
        //身份证姓名
        $params['realName']=post('realName');
        
        $where['card']=$params['cardNo'];
        $where['uid[!]']=$uid;
        $card_uid = (new \app\model\user_attach())->is_have($where);
        //cs((new \app\model\user_attach())->log(),1);
        if($card_uid){
            error('身份证号码已被他人认证',400);
        }

        //正面      
        $params['card_face'] = post('card_face');
        $params['card_bg'] = post('card_bg');
        
        //背面
        empty($params['cardNo']) && error('请传身份证号',400);
        empty($params['realName']) && error('请传真实姓名',400);

        if(plugin_is_open('gasmrz')){
            if($GLOBALS['user']['quhao']=='86'){
                $sfrz_S = new \app\service\sfrz();
                $sfrz_S->APISTORE_POST($params);
            }
        }

        $data['card'] = $params['cardNo'];
        $data['name'] = $params['realName'];
        $data['card_face'] = $params['card_face'] ? $params['card_face'] : '';
        $data['card_bg'] = $params['card_bg'] ? $params['card_face'] : '';
        $user_attach_M = new \app\model\user_attach();
        $res = $user_attach_M->up($uid,$data);
        empty($res) && error('认证上传失败',400);


        if(c('is_wcsmrz')==1){
            $new_duty_S = new \app\service\new_duty();
            $new_duty_S->paid_reward($uid,'wcsmrz'); //新手任务-完成实名认证
        }
       

        //$this->complete_reward();
        return true;
        //"code":"1002","data":"","end":"","message":"请求身份证号不标准：身份证号为空或者不符合身份证校验规范","pageNum":"","pageSize":"","param":"","result":"","start":"","total":"","tradeNo":"20190329155030_OmZ2439W_18091779"
    }


    /*会员中心 - 设置 关联支付宝*/
    public function alipay_saveedit(){
        if($GLOBALS['user']['is_real']==1){
            error('已完善资料无法修改',400);
        }
        (new \app\validate\UcenterValidate())->goCheck('scene_alipay'); 
        $uid = $GLOBALS['user']['id'];
        $data['alipay'] = post('alipay');
        $data['alipay_name'] = post('alipay_name');
        $data['alipay_pic'] = post('alipay_pic'); 
        $user_attach_M = new \app\model\user_attach();
        $res = $user_attach_M->up($uid,$data);
        empty($res) && error('修改失败',400);
        $this->complete_reward();
        return $res;
    }

    /*会员中心 - 设置 关联微信*/
    public function wechat_saveedit(){
        (new \app\validate\UcenterValidate())->goCheck('scene_wechat'); 
        $uid = $GLOBALS['user']['id']; 
        $data['wechat'] = post('wechat');
        $data['wechat_pic'] = post('wechat_pic');
        $user_attach_M = new \app\model\user_attach();
        $res = $user_attach_M->up($uid,$data);
        empty($res) && error('修改失败',400);
        $this->complete_reward();
        return $res;
    }
    
    /*收款账号*/
    public function bind_account(){ 
        if($GLOBALS['user']['is_real']==1){
            error('已完善资料无法修改',400);
        } 
        (new \app\validate\UcenterValidate())->goCheck('scene_bind_account');
        $uid = $GLOBALS['user']['id'];     
        $data['bank_card'] = post("bank_card"); //银行账号
        $data['bank_name'] = post("bank_name"); //银行卡户名
        $data['bank'] = post("bank"); //开户银行
        $data['bank_network'] = post("bank_network"); //网点名     
        $data['bank_province'] = post("bank_province"); //网点名     
        $data['bank_city'] = post("bank_city"); //网点名     

        $user_attach_M = new \app\model\user_attach();
        $res = $user_attach_M->up($uid,$data);
        empty($res) && error('修改失败',400);
        $this->complete_reward();
        return $res;
    }

    /*完善基本资料*/
    public function complete_info(){ 
    (new \app\validate\UcenterValidate())->goCheck('scene_complete_info'); 
        $uid = $GLOBALS['user']['id'];
        $data['avatar'] = post('avatar');
        $data['nickname'] = post('nickname');
        $data['sex'] = post('sex');

        $user_M = new \app\model\user();
        $res = $user_M->up($uid,$data);
        empty($res) && error('修改失败',400);
        
        if($GLOBALS['user']['im']){
            $im = new \app\service\im();  
            $im->edit_info($GLOBALS['user']['im'],$data['nickname'],$data['avatar'],$data['sex']);
        }

        $new_duty_S = new \app\service\new_duty();
        $new_duty_S->paid_reward($uid,'wcgrzl'); //新手任务-完成个人资料

        return $res;
    }


    //设置状态
    public function address_bank(){
        $uid = $GLOBALS['user']['id'];

        $user_address_M = new \app\model\user_address();
        $is_have_address = $user_address_M ->is_have_address($uid);//是否设置收货地址
        $users=user_info($uid);
        $bank_card = $users['bank_card'];
        $bank_name = $users['bank_name'];
        
        if($bank_card!=''  && $bank_name!=''){
            $is_have_bank = true;
        }else{
            $is_have_bank = false;
        }

        $data['is_have_address'] = $is_have_address;
        $data['is_have_bank'] = $is_have_bank;

        return $data;
    }


    /*用户在redis中的信息 postman中head头里传uid*/
    public function user_all(){
        /* $uid = $GLOBALS['user']['id'];
        $where['id']=$uid;
        $where['username']=$GLOBALS['user']['username'];
        $user = $this->user_M->have($where);
        empty($user) && error('非正常登录,身份不合法',602);

        $ar = $this->user_M->find_all($uid);
        empty($ar) && error('非正常登录,身份不合法',602);
        unset($ar['password']);
        unset($ar['pay_password']);
        $ar['user_key']=$GLOBALS['user']['user_key'];
        $ar['im_sig']=$GLOBALS['user']['im_sig']; */
        $rating=(new \app\model\rating)->find($GLOBALS['user']['rating'],['flag','piclink']);
        $GLOBALS['user']['flag']=$rating['flag'];
        $GLOBALS['user']['piclink']=$rating['piclink'];
        $GLOBALS['user']['bank_card']=(new \app\model\user_attach())->find($GLOBALS['user']['uid'],'bank_card');
        $GLOBALS['user']['card']=(string)$GLOBALS['user']['card'];

        //用户中心要显示待付款，待发货等的数量
        $order_M = new \app\model\order();
        $where_1['uid'] = $GLOBALS['user']['id'];
        $where_1['is_pay'] = 0;
        $where_1['status[!]'] = '已关闭';
        $my_order_num_1 = $order_M->new_count($where_1);


        $where_2['uid'] = $GLOBALS['user']['id'];
        $where_2['is_pay'] = 1;
        $where_2['status'] = '已支付';
        $my_order_num_2 = $order_M->new_count($where_2);

        $where_3['uid'] = $GLOBALS['user']['id'];
        $where_3['is_pay'] = 1;
        $where_3['status'] = '已发货';
        $my_order_num_3 = $order_M->new_count($where_3);

        $where_4['uid'] = $GLOBALS['user']['id'];
        $where_4['is_pay'] = 1;
        $where_4['status'] = '已完成';
        $where_4['is_review'] = 0;
        $my_order_num_4 = $order_M->new_count($where_4);

        $where_5['uid'] = $GLOBALS['user']['id'];
        $where_5['is_return'] = 1;
        $my_order_num_5 = $order_M->new_count($where_5);

        $GLOBALS['user']['my_order_num_1'] = $my_order_num_1 ? $my_order_num_1 : '0';
        $GLOBALS['user']['my_order_num_2'] = $my_order_num_2 ? $my_order_num_2 : '0';
        $GLOBALS['user']['my_order_num_3'] = $my_order_num_3 ? $my_order_num_3 : '0';
        $GLOBALS['user']['my_order_num_4'] = $my_order_num_4 ? $my_order_num_4 : '0';
        $GLOBALS['user']['my_order_num_5'] = $my_order_num_5 ? $my_order_num_5 : '0';

        
        $tid_cn = $this->user_M->find($GLOBALS['user']['tid'],['nickname','username','avatar']); //推荐人用户名
        $GLOBALS['user']['tid_cn']=$tid_cn;
        return $GLOBALS['user'];
    }

    public function tid_info()
    {
        $tid_cn = $this->user_M->find($GLOBALS['user']['tid'],['nickname','username','avatar']); //推荐人用户名
        return $tid_cn;
    }


    public function complete_reward()
    {
        if($GLOBALS['user']['is_real']!=1){
            $uid = $GLOBALS['user']['id'];
            flash_god($uid);
            $user_attach_M = new \app\model\user_attach();
            $user_ar = $user_attach_M->find($uid);
            if($user_ar){
                if($user_ar['card']!='' && $user_ar['name']!='' &&  $user_ar['alipay']!='' &&  $user_ar['alipay_name']!='' &&  $user_ar['wechat']!='' &&  $user_ar['alipay_pic']!='' && $user_ar['wechat_pic']!='' && $user_ar['bank_card']!='' && $user_ar['bank_name']!='' && $user_ar['bank']!='' && $user_ar['bank_network']!='' && $user_ar['bank_province']!='' && $user_ar['bank_city']!=''){
                    $this->user_M->up($uid,['is_real'=>1]);
                    (new \app\service\coin())->gift(7,$uid);
                }
            }
        }
    }

    //极光ID
    public function aurora()
    {
        $data['aurora_id']=post('aurora_id');
        if($data['aurora_id']){
        $this->user_M->up($GLOBALS['user']['id'],$data);
        }
        return '修改成功';
    }

    

}






