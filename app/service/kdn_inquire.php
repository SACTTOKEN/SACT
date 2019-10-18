<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-16 15:33:08
 * Desc: 快递鸟查询接口
 */
namespace app\service;

class kdn_inquire{
    public $EBusinessID; //电商ID
    public $AppKey;
    public $ReqURL;

    public function __construct()
    {
        
        $account=cc('account','kdn_inquire');
        $this->EBusinessID = $account['EBusinessID'];
        $this->AppKey = $account['AppKey'];
        $this->ReqURL = $account['ReqURL'];
    }

    public function index($order_ar)
    {
        if(!$order_ar){
            return;
        }
        if($order_ar['mail_oid']==""){
            return;
        }
        $mail_ar=(new \app\model\mail())->have(['sid'=>$order_ar['sid']]);
        if(!isset($mail_ar['title_en'])){
            return;
        }
        $logisticResult=$this->getOrderTracesByJson($mail_ar['title_en'],$order_ar['mail_oid']);
        $ar = json_decode($logisticResult,true);
        if(isset($ar['Traces'])){
            $ar['Traces']=array_reverse($ar['Traces']);
        }
        //$arr = array_reverse($ar['Traces']);
        return $ar;
    }
    
    /**
     * Json方式 查询订单物流轨迹
     */
    function getOrderTracesByJson($ship_code,$logistic_code){
        if(empty($ship_code) || empty($logistic_code)){
            echo "<h2>参数错误</h2>";
            exit();
        }
        $requestData= "{'OrderCode':'','ShipperCode':'".$ship_code."','LogisticCode':'".$logistic_code."'}";
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
        $result=$this->sendPost($this->ReqURL, $datas); 
        return $result;
    }
    
    /**
     *  post提交数据 
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据 
     * @return url响应返回的html
     */
    function sendPost($url, $datas) {
        $temps = array(); 
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);    
        } 
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80; 
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
            break;
            }
        }
        while (!feof($fd)) {
        $gets.= fread($fd, 128);
        }
        fclose($fd);  
        
        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容   
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }
}