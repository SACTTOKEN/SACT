<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-27 11:44:36
 * Desc: 微信相关类
 * 测试地址：https://mp.weixin.qq.com/debug/cgi-bin/sandbox?t=sandbox/login
 * 调BUG地址：https://mp.weixin.qq.com/debug
 */
namespace app\service;

class wechat{  

    public $appID = '';       //wxeac44855efeb5175
    public $appsecret = '';  //d6af786182ac02693f78378718ddb3e4
    public $redirect_uri;

    public function  __construct(){
        $web_name = renew_c('wxhdwz');  //mobile.mm80.cn
        $this->appID = renew_c('appid');
        $this->appsecret = renew_c('appsec');
        $this->redirect_uri =  "http://".$web_name."/common/wx/wx_login"; //http://api.mm80.cn/common/wx/wx_login    
    }

    //验证成功后，返回$echoStr字符串给微信处理 
    public function valid()
    {
        $echoStr = get("echostr");
        if($this->checkSignature()){ 
            echo $echoStr; 
            exit;
        }
    }

    private function checkSignature()
    {
        $token = c('token');
        if (!$token) {
            throw new Exception('TOKEN is not defined!');
        }
        $signature = get("signature");
        $timestamp = get("timestamp");
        $nonce = get("nonce");
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }


//=====================自动回复==========================
    public function responseMsg()
    {      

        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
      
        if (!empty($postStr)){
                
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        
            switch ($postObj->MsgType){
                case 'event':
                    if($postObj->Event=='subscribe'){
                        $this->wlw_subscribe($postObj);
                    }
                    break;
                case 'text':
                    $this->wlw_text($postObj); 
                    break;
                case 'image':
                    $this->msgback($postObj,"您发的图片很漂亮!");
                    break;
                case 'location':
                    $this->msgback($postObj,"您的位置真是一个风水宝地!");
                    break;
                case 'voice':
                    $this->msgback($postObj,"哇!余音绕梁");
                    break;
                default:
                    $this->msgback($postObj,"我们会尽快回复您!");
                    break;
            }

        }
    }

    //响应关键字自动回复
    public function wlw_text($postObj){
        $wxtextM = new \app\model\wx_text();
        $wx_material_M = new \app\model\wx_material();

        $keyword = trim($postObj->Content);

        $where['keyword'] = $keyword;
        $rs = $wxtextM->have($where);
        if(!$rs){
            $where2['keyword[~]'] = $keyword;
            $where2['is_like'] = 1;
            $rs = $wxtextM->have($where2);
        }

        if(!$rs){return;}

        switch($rs['types']) {
            case '0':
                $this->msgback($postObj, $rs['content']);
            break;
            case '1':                
                $ar = $wx_material_M->find($rs['material']);
                $image = $this->piclink_format($ar['piclink']);             
                    $new_rs[] = [
                        "Title" => $ar['title'], 
                        "Description" => $ar['content'], 
                        "PicUrl" => $image, 
                        "Url" => $ar['links']
                    ];
                echo $this->transmitNews($postObj, $new_rs);
            break;         
        }

    }

    /*给前端vue的图片带/api的要换成网址，微信发图文用*/
    public function piclink_format($piclink){
        $web = renew_c('wxhdwz');
        if(strpos($piclink,'/api')!==false){ 
            return 'http://'.str_replace('/api',$web,$piclink);
        }
        return $piclink;
    }



    //首次关注回复
    public function wlw_subscribe($postObj){

         txt_log($postObj,'wxwx');

        // $wx_openid_M = new \app\model\wx_openid();
        // $tjm = $postObj->EventKey;
        // $tjm = str_replace("qrscene_",'',$tjm);
        // $where['tjm'] = $tjm;
        // $is_have = $wx_openid_M->is_have($where);
        // if(empty($is_have)){
        //     $data['tjm'] = $tjm;
        //     $data['openid'] = $postObj->FromUserName;
        //     $wx_openid_M->save($data);
        // }

        $wxfollowM = new \app\model\wx_follow();
        $wx_material_M = new \app\model\wx_material();
        $rs = $wxfollowM->find(1); 
        switch($rs['types']) {
            case '0':
                $this->msgback($postObj, $rs['content']);
            break;
            case '1':                
                $ar = $wx_material_M->find($rs['material']);
                $image = $this->piclink_format($ar['piclink']);             
                    $new_rs[] = [
                        "Title" => $ar['title'], 
                        "Description" => $ar['content'], 
                        "PicUrl" => $image, 
                        "Url" => $ar['links']
                    ];
                echo  $this->transmitNews($postObj, $new_rs);
            break;         
        }      
    }


    /* 模板消息
     * $userAppid 用户的open_id
     * $template_id 模板id
     * $url 点击详情地址
     * $first 第一句话
     * $arrWord 中间的话数组
     * $remark 提示语句
     * */
    function wlw_wxnotice($userAppid, $template_id, $url, $first, $arrWord, $remark='')
    {
    $params = array('touser' => $userAppid, 'template_id' => $template_id, 'url' => $url, 'topcolor' => '#173177');
    $params['data'] = array();
    $params['data']['first'] = array('value' => $first, 'color' => '#173177');

    foreach ($arrWord as $key => $value) {
        $keyword_Num = "keyword" . ($key + 1);
        $params['data'][$keyword_Num] = array('value' => $value, 'color' => '#173177');
    }

    $params['data']['remark'] = array('value' => $remark, 'color' => '#173177');
    $post = json_encode($params);
    $access_token = $this->getAccessToken($this->appID, $this->appsecret);   
    $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($post))
    );
    $result = curl_exec($ch);
    return $result;
    }


//=====================生成会员二维码,扫码关注公众号,有推荐码的参数==========================
    public function gzh_ewm($uid){

        $user_M = new \app\model\user();
        $share = $user_M->find($uid,'share');  
        if(empty($share)){return false;} 
        $code_url ='/resource/image/gzh_ewm/'.$share.'.png';
        $access_token = $this->getAccessToken($this->appID, $this->appsecret);
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;

        $qrcode = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str":"'.$share.'"}}}';        
        $result = $this->https_request($url,$qrcode);
        $jsoninfo = json_decode($result, true);
        $ticket = $jsoninfo["ticket"];
        $url2 = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
        $file = $this->https_request($url2);
        $ewm = IMOOC."public/resource/image/gzh_ewm/".$share.".png";
        $local_file = fopen($ewm,'w');      
        fwrite($local_file, $file);
        fclose($local_file);    
        if(file_exists($ewm)){
            return $code_url;
        }else{
            return false;
        }       
    }


//=====================微信菜单列表==========================

    public function getAccessToken( $appid, $appsecret ) {
        //判断是否过了缓存期,accesstoken是全局的，不要存cookie,改存redis,后期修改
        if(!empty(cookie('test_access_token'))){
            return cookie('test_access_token');
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
        $return = $this->https_request($url);
        $return = json_decode( $return, 1 );
        $web_expires = time() + 7000; // 提前200秒过期

        set_cookie('test_access_token',$return['access_token'],60*60*24);
        return $return[ 'access_token' ];
    }


    
    public function getWxType($num=0){
        $ar = [
            '0'=>'click',
            '1'=>'view',
            '2'=>'scancode_push',//扫码推事件
            '3'=>'scancode_waitmsg',//扫码带消息
            '4'=>'pic_sysphoto',  //系统拍照
            '5'=>'pic_photo_or_album', //弹出拍照或发图
            '6'=>'pic_weixin',//弹出微信相册
            '7'=>'location_select', //弹出地理位置选择器
            '8'=>'miniprogram', //小程序 "type":"miniprogram",  "name":"wxa", "url":"http://mp.weixin.qq.com", "appid":"wx286b93c14bbf93aa","pagepath":"pages/lunar/index"
        ];
        return $ar[$num];
    }



    public function convertMenu(){
        $wxmenuM = new \app\model\wx_menu();      
        $p_list = $wxmenuM->list_cate(0);


        $p_arr = [];

        foreach ( $p_list as $key => $v ) {

            $p_arr[ $key ][ 'name' ] = $v[ 'title' ];
            $v[ 'types' ] = $this->getWxType( $v[ 'types' ] );

            //获取子菜单
            $s_list = $wxmenuM->list_cate($v['id']);

            if ( $s_list ) {
                foreach ( $s_list as $kk => $vv ) {
                    $s_arr = [];
                    $s_arr[ 'name' ] = $vv[ 'title' ];
                    $s_arr[ 'type' ] = $this->getWxType( $vv[ 'types' ] );
                    // click类型
                    if ( $s_arr[ 'type' ] == 'click' ) {
                        $s_arr[ 'key' ] = $vv[ 'value' ];
                    } elseif ( $s_arr[ 'type' ] == 'view'  ) {
                        $s_arr[ 'url' ] = $vv[ 'value' ];
                    } elseif( $s_arr['type'] == 'miniprogram'){
                        $s_arr[ 'url' ] = $vv['value'];             //'http://mp.weixin.qq.com';
                        $s_arr[ 'appid' ] = $vv['appid'];          //'wx286b93c14bbf93aa';
                        $s_arr[ 'pagepath' ] = $vv['pagepath'];   //'pages/lunar/index';
                    } else {
                        $s_arr[ 'key' ] = $vv[ 'value' ];
                    }
                    $s_arr[ 'sub_button' ] = array();
                    if ( $s_arr[ 'name' ] ) {
                        $p_arr[ $key ][ 'sub_button' ][] = $s_arr;
                    }
                }
            } else {
                $p_arr[ $key ][ 'type' ] = $v[ 'types' ];
                // click类型
                if ( $p_arr[ $key ][ 'type' ] == 'click' ) {
                    $p_arr[ $key ][ 'key' ] = $v[ 'value' ];
                } elseif ( $p_arr[ $key ][ 'types' ] == 'view' ) {
                    //跳转URL类型
                    $p_arr[ $key ][ 'url' ] = $v[ 'value' ];
                } else {
                    //其他事件类型
                    $p_arr[ $key ][ 'key' ] = $v[ 'value' ];
                }
            }
            $key++;
        }
        $menu = array('button' => $p_arr);
        return json_encode($menu, JSON_UNESCAPED_UNICODE );
    }


    /*生成微信菜单*/
    public function pubMenu(){
        $wxmenuM = new \app\model\wx_menu();      
        $p_list = $wxmenuM->list_cate(0);
        if(count($p_list)<1){
            error('没有可发布的菜单',400);
        }
        
        $poster = $this->convertMenu();

        //cs($poster,1);

        $access_token = $this->getAccessToken($this->appID, $this->appsecret);

        if(!$access_token ) {
            error('获取access_token失败',400);
        }

        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        $return = $this->https_request( $url,$poster );
        $return = json_decode( $return, 1 );
        if ( $return[ 'errcode' ] == 0 ) {
            //echo '微信菜单已成功生成'.$access_token;
            return true;
        } else {
            error( $return['errcode'],400); //$access_token."@@@"
        }
    }

    /*微信登录step1: 请求认证code*/
    public function wechat_code($tshare=''){
        $tshare = $tshare ? $tshare : 1;      
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appID."&redirect_uri=".$this->redirect_uri."&response_type=code&scope=snsapi_base&state=".$tshare."#wechat_redirect";
        return $url; //前后端分离 //
        //header('Location:'.$url); //回调：common/wx/wx_login       
    }

    /*微信登录step3:获取openid*/
    public function wechat_openid($code){
        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appID."&secret=".$this->appsecret."&code=".$code."&grant_type=authorization_code";
        $oauth2 = $this->https_request($oauth2Url);
        $oauth2 = json_decode($oauth2,true);
       
        $openid = $oauth2['openid'];
        return $openid;
    }

    /*获取微信用户信息*/
    public function wechat_userinfo($openid){
        $access_token = $this->getAccessToken($this->appID, $this->appsecret);
        $get_user_info_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $userinfo = $this->https_request($get_user_info_url);
        $userinfo = json_decode($userinfo,true);   # code...
        return $userinfo;
    }
       
    /*微信登录 - 数据层实现*/
    public function wechat_login($userinfo){       
        $userM = new \app\model\user(); 
        empty($userinfo['openid']) && error('openid丢失',400);
        $res = $userM->find_by_openid($userinfo['openid']);

        if(empty($res)){
            if(!empty($userinfo['openid'])){
                $data['nickname'] = $userinfo['nickname'];
                //$data['sex'] = ($userinfo['sex']==1)?'男':'女';
                $data['avatar'] =$userinfo['headimgurl'];
                $data['openid'] =$userinfo['openid'];
                $new_uid = $userM->save($data); //"生成新用户，用户ID为".$new_uid;
             
                //写入redis队列，排队发送微信模板消息
                $tid = 0; //推荐人ID
                $redis = new \core\lib\redis();
                $params_ar['tid'] = $tid ? $tid : 0;
                $params_ar['uid'] = $new_uid ? $new_uid : 0;
                $params_ar['action'] = 'user_reg';
                $params = json_encode($params_ar,JSON_UNESCAPED_UNICODE);
                $redis->lpush('mbxx',$params); 
                echo "生成新用户，用户ID为".$new_uid;
                exit();
            }
        }else{
            if($res['openid'] == $userinfo['openid']){
                $new_uid =  $res['id'];    
                echo "用户".$new_uid."已存在,登录成功";
                exit();  
            }            
        } 
        /**
         *   将用户信息写入redies
         *   生成32位用户token,同时存入cookie,验证浏览器来源。
         *   同时生成16位用户key,参与签名不传输，验证参数
         */
      
        $auth['id'] = $new_uid;
        $token=new \app\service\token();  
        $res = $token->usertoken($auth);

        $web_url = CC('web_config','api');
        header("Location: http://".$web_url);
    }


//==========================以下为官方提供,请勿修改===============================
    //文本回复格式
    public function msgback($postObj,$content){
        $content = str_replace('{换行符}',"\n",$content);
        echo '<xml>
            <ToUserName><![CDATA['.$postObj->FromUserName.']]></ToUserName>
            <FromUserName><![CDATA['.$postObj->ToUserName.']]></FromUserName>
            <CreateTime>'.time().'</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA['.$content.']]></Content>
            </xml>';
    }

    //图文回复格式
    public function transmitNews($object, $newsArray)
    {
    if(!is_array($newsArray)){
    return;
    }
    $itemTpl = "<item>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <PicUrl><![CDATA[%s]]></PicUrl>
    <Url><![CDATA[%s]]></Url>
    </item>
    ";
    $item_str = "";
    foreach ($newsArray as $item){
    $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
    }
    $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>%s</ArticleCount>
    <Articles>".$item_str."</Articles>
    </xml>";

    $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
    return $result;
    }  


    function https_request($url, $data = null)
    {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
    }


} //END