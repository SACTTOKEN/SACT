<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: 二维码控制器
 */

namespace app\ctrl\mobile;
use app\validate\IDMustBeRequire;

use app\model\banner as BannerModel;

class qr extends BaseController{
	
	public $bannerM;
	public $user_attach_M;
	public function __initialize(){
		$this->bannerM = new BannerModel();
        $this->user_attach_M = new \app\model\user_attach();
	}

    //入口
    public function entrance(){
        (new IDMustBeRequire())->goCheck();
        //$id = post('id');
        //flash_god($id);
        $id=$GLOBALS['user']['id'];
        $ewm_type = post('ewm_type');  //  1:APP端显示(APP,网页)   2:公众号端显示  
        empty($ewm_type) && error('二维码类型参数丢失',400);
        $ewm_app_show = c('ewm_app_show');
        $ewm_gzh_show = c('ewm_gzh_show');
        if($ewm_type==1){
            if($ewm_app_show == '网页'){
                return $this->web($id);
            }
            if($ewm_app_show=='公众号'){
                return $this->gzh($id);
            }
        }        
        if($ewm_type==2){
            if($ewm_gzh_show=='网页'){
                return $this->web($id);
            }
            if($ewm_gzh_show=='公众号'){
                return $this->gzh($id);
            }
        }
        
        error('未配置二维码',400);
    }



    /*网页二维码*/
    public function web($id)
    {
        // (new IDMustBeRequire())->goCheck();
        // $id=post('id');
        $user_M = new \app\model\user();
        $user = $user_M->find($id);       
        $wy_ewm=$this->user_attach_M->find($user['id'],'wy_ewm');

        if(empty($wy_ewm)){
            $mobile_web = c('wx_mobile_web'); //$wxhdwz=c("wxhdwz"); http://www.szpg88.com
            //cs($mobile_web,1);
            $url=$mobile_web.'/star?tshare='.$user['username'];
            //$is_open_oss = c('kqoss');
            $errorCorrectionLevel = 'm';//容错级别   
            $matrixPointSize = 6;//生成图片大小 
            $wy_ewm='resource/image/wy_wem/'.$user['id'].'.png';
            \core\lib\QRcode::png($url, $wy_ewm, $errorCorrectionLevel, $matrixPointSize, 2);    
            $this->user_attach_M->up($user['id'],['wy_ewm'=>'/api'.$wy_ewm]);
        }

        $my_name = $user['nickname'] ? $user['nickname'] : $user['username'];

        $data['wy_ewm']=$wy_ewm.'?id='.rand(0,1000);
        $data['banner']=$this->bannerM->list_show('ewm_bg');
        foreach($data['banner'] as &$vo){
            $vo['piclink']=str_replace("https://","http://",$vo['piclink']);
        }
        $data['user']['avatar']= $user['avatar'];
        $data['user']['title1']='我是'.$my_name;
        $data['user']['title2']='我的推广码：'.$user['username'];    //'我为'.c('head').'代言'
        return $data; 
    }

    /*公众号二维码*/
	public function gzh($id)
	{
        // (new IDMustBeRequire())->goCheck();
        // $id=post('id');
        $user_M = new \app\model\user();
		$user = $user_M->find($id);  
      
        $wx_ewm=$this->user_attach_M->find($id,'wx_ewm');
        if(!$wx_ewm){
            $wechat_S = new \app\service\wechat();
            $qrcode = $wechat_S -> gzh_ewm($id);   //'/resource/image/gzh_ewm/6.png';
            if(!$qrcode){
                error('生成二维码错误',400);
            }
            $qrcode='/api'.$qrcode;
            $this->user_attach_M->up($id,['wx_ewm'=>$qrcode]);
            $wx_ewm = $qrcode;
        }
        

        $my_name = $user['nickname'] ? $user['nickname'] : $user['username'];
        $data['wy_ewm'] = $wx_ewm.'?id='.rand(0,1000);
        $data['banner']=$this->bannerM->list_show('ewm_bg');
        foreach($data['banner'] as &$vo){
            $vo['piclink']=str_replace("https://","http://",$vo['piclink']);
        }
        $data['user']['avatar']=$user['avatar'];
        $data['user']['title1']='我是'.$my_name;
        $data['user']['title2']='我的推广码：'.$user['username'];
        return $data; 
	}

    /*test*/
	public function wechat()
	{
        $web = c('wx_mobile_web');
        $uid = $GLOBALS['user']['uid'];
        $uid = $uid ? $uid : post('uid');
        $wechat_S = new \app\service\wechat();
        $wechat_S -> gzh_ewm($uid);
        return $web."/resource/image/gzh_ewm/".$uid.".png";
	}

    /*产品二维码*/
	public function product()
	{
		
	}


    public function app()
    {
        //return c('app_down_ewm');
    }


    



}