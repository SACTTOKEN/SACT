<?php
//----------------------------------
// 聚合数据-油卡充值API调用类
//----------------------------------


namespace app\service;

class juhe_youka {
 
    private $appkey;
    private $openid;
    private $submitUrl = 'http://op.juhe.cn/ofpay/sinopec/onlineorder';
 
    private $staUrl = 'http://op.juhe.cn/ofpay/sinopec/ordersta';
 
    public function __construct(){
        $this->appkey = c('juhe_yk_appkey');     //'4bf6a38cc94ce6d46ba1127a18178157';
        $this->openid = c('juhe_openid');       //'JH7164f37b3fd989d0e45f0c9b60327b23';
    }
 
 
    /**
     * 1提交加油卡充值
     * @param  [string] $proid   [产品ID] 10001(中石化100元加油卡) 10002(中石化200元加油卡)  10003(中石化500元加油卡)  10004(中石化1000元加油卡)
     * @param  [int] $cardnum [充值数量] 1
     * @param  [string] $orderid  [自定义单号]
     * @param  [string] $game_userid  [加油卡卡号]
     * @param  [string] $gasCardTel  [持卡人手机号]
     * @return  [array]
     */
    public function cardcz($proid,$cardnum,$orderid,$game_userid,$gas_tel){
        $sign = md5($this->openid.$this->appkey.$proid.$cardnum.$game_userid.$orderid);//校验值计算
        $params = array(
            'key' => $this->appkey,
            'proid'   => $proid,
            'cardnum'   => $cardnum,
            'game_userid'   => $game_userid,
            'gasCardTel'   => $gas_tel,
            'orderid'   => $orderid,
            'sign' => $sign
        );
        $paramstring = http_build_query($params); //拼成get请求url串
        $content = $this->juhecurl($this->submitUrl,$paramstring);
        $result = json_decode($content,true);
        return $result;
    }
 
    /**
     * 2查询订单的充值状态
     * @param  [string] $orderid [自定义单号]
     * @return  [array]
     */
    public function sta($orderid){
        $params = 'key='.$this->appkey.'&orderid='.$orderid;
        $content = $this->juhecurl($this->staUrl,$params);
        $result = json_decode($content,true);
        return $result; // 成功：$result['error_code']=='0'
    }
 

    /**3账户余额查询*/   
    public function yue(){
        $url = "http://op.juhe.cn/ofpay/sinopec/yue";
        $ts = time();
        $params = array(
              "timestamp" => $ts,//当前时间戳，如：1432788379
              "key" => $appkey,//应用APPKEY(应用详细页查询)
              "sign" => md5($this->openid.$this->appkey.$ts),//校验值，md5(OpenID+key+timestamp)，OpenID在个人中心查询
        );
        $paramstring = http_build_query($params);
        $content = juhecurl($url,$paramstring);
        $result = json_decode($content,true);
        if($result){
            if($result['error_code']=='0'){
                print_r($result);
            }else{
                echo $result['error_code'].":".$result['reason'];
            }
        }else{
            echo "请求失败";
        }
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
