<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-14 17:25:08
 * Desc: 订单
 */
namespace app\ctrl\mobile;

class weixin
{
	public function __initialize(){
	}

	//确认订单号
	public function config()
	{
        $url=urldecode(post('url'));
        $data['appId']=c('appid');
        $data['timestamp']=time();
        $data['nonceStr']="RdWdLoch0FHg7IcD";
        $sjsapi_ticket=(new \app\service\wechat())->get_sjsapi_ticket();
        $signature=sha1("jsapi_ticket=".$sjsapi_ticket."&noncestr=".$data['nonceStr']."&timestamp=".$data['timestamp']."&url=".$url."");
        $data['signature']=$signature;
        return $data;
    }

}