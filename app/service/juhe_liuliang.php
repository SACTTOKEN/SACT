<?php
//----------------------------------
// 聚合数据-手机流量充值API调用类
//----------------------------------


namespace app\service;

class juhe_liuliang {
 
    private $appkey;
 
    private $openid;
 
    private $telCheckUrl = 'http://v.juhe.cn/flow/telcheck'; //检测号码支持的流量套餐
 
    private $submitUrl = 'http://v.juhe.cn/flow/recharge';//提交流量充值
 
    private $staUrl = 'http://v.juhe.cn/flow/ordersta';//订单状态查询
 
    public function __construct(){
        $this->appkey =  renew_c('juhe_ll_appkey');  //'37cf0cdb970b34cdb4cc7caec55b8d9d';
        $this->openid =  renew_c('juhe_openid');    //'JH7164f37b3fd989d0e45f0c9b60327b23';
    }
 
    /**
     * 根据手机号码查流量套餐
     * @param  string $mobile   [手机号码]
     * @return  boolean
     */
    public function telcheck($mobile){
        $params = 'key='.$this->appkey.'&phone='.$mobile;
        $content = $this->juhecurl($this->telCheckUrl,$params);
        $result = $this->_returnArray($content);
       return $result;
    }

    /**
     * 提交话费充值
     * @param  [string] $mobile   [手机号码]
     * @param  [int] $pid [流量套餐ID]
     * @param  [string] $orderid  [自定义单号]
     * @return [array]
     */
    public function telcz($mobile,$pid,$orderid){
        $sign = md5($this->openid.$this->appkey.$mobile.$pid.$orderid);//校验值计算
        $params = array(
            'key' => $this->appkey,
            'phone'   => $mobile,
            'pid'   => $pid,
            'orderid'   => $orderid,
            'sign' => $sign
        );
        $content = $this->juhecurl($this->submitUrl,$params,1);
        return $this->_returnArray($content);
    }
 
    /**
     * 查询订单的充值状态
     * @param  [string] $orderid [自定义单号]
     * @return  [array]
     */
    public function sta($orderid){
        $params = 'key='.$this->appkey.'&orderid='.$orderid;
        $content = $this->juhecurl($this->staUrl,$params);
        return $this->_returnArray($content);
    }
 
    /**
     * 将JSON内容转为数据，并返回
     * @param string $content [内容]
     * @return array
     */
    public function _returnArray($content){
        return json_decode($content,true);
    }
 
    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    public function juhecurl($url,$params=false,$ispost=0){
        $httpInfo = array();
        $ch = curl_init();
 
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }






    /*
     * 接受话费\加油卡\流量充值业务 异步通知参数 参考示例
     */
    // public function notify(){
    //     $appkey = "************************"; //您申请的数据的APIKey
         
    //     $sporder_id = addslashes($_POST['sporder_id']); //聚合订单号
    //     $orderid = addslashes($_POST['orderid']); //商户的单号
    //     $sta = addslashes($_POST['sta']); //充值状态
    //     $sign = addslashes($_POST['sign']); //校验值
    //     $local_sign = md5($appkey.$sporder_id.$orderid); //本地sign校验值      
    //     if ($local_sign == $sign) {
    //         if ($sta == '1') {
    //             //充值成功,根据自身业务逻辑进行后续处理
    //         } elseif ($sta =='9') {
    //             //充值失败,根据自身业务逻辑进行后续处理
    //         }
    //     }
    // }







}
