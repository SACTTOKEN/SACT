<?php
//----------------------------------
// 聚合数据-手机话费充值API调用类
//----------------------------------


namespace app\service;

class juhe_huafei {
 
    private $appkey;
 
    private $openid;
 
    private $telCheckUrl = 'http://op.juhe.cn/ofpay/mobile/telcheck';
 
    private $telQueryUrl = 'http://op.juhe.cn/ofpay/mobile/telquery';
 
    private $submitUrl = 'http://op.juhe.cn/ofpay/mobile/onlineorder';
 
    private $staUrl = 'http://op.juhe.cn/ofpay/mobile/ordersta';
 
    public function __construct(){
        $this->appkey = c('juhe_hf_appkey');//'4bf6a38cc94ce6d46ba1127a18178157';
        $this->openid = c('juhe_openid');   //'JH7164f37b3fd989d0e45f0c9b60327b23';
    }
 
    /**
     * 根据手机号码及面额查询是否支持充值
     * @param  string $mobile   [手机号码]
     * @param  int $pervalue [充值金额]
     * @return  boolean
     */
    public function telcheck($mobile,$pervalue){
        $params = 'key='.$this->appkey.'&phoneno='.$mobile.'&cardnum='.$pervalue;
        $content = $this->juhecurl($this->telCheckUrl,$params);
        $result = $this->_returnArray($content);
        if($result['error_code'] == '0'){
            return true;
        }else{
            return false;
        }
    }
 
    /**
     * 根据手机号码和面额获取商品信息
     * @param  string $mobile   [手机号码]
     * @param  int $pervalue [充值金额]
     * @return  array
     */
    public function telquery($mobile,$pervalue){
        $params = 'key='.$this->appkey.'&phoneno='.$mobile.'&cardnum='.$pervalue;
        $content = $this->juhecurl($this->telQueryUrl,$params);
        return $this->_returnArray($content);
    }
 
    /**
     * 提交话费充值
     * @param  [string] $mobile   [手机号码]
     * @param  [int] $pervalue [充值面额]
     * @param  [string] $orderid  [自定义单号]
     * @return  [array]
     */
    public function telcz($mobile,$pervalue,$orderid){
        $sign = md5($this->openid.$this->appkey.$mobile.$pervalue.$orderid);//校验值计算
        $params = array(
            'key' => $this->appkey,
            'phoneno'   => $mobile,
            'cardnum'   => $pervalue,
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
}
