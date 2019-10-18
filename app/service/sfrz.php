<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-29 15:08:09
 * Desc: 身份认证
 */
namespace app\service;

class sfrz{
    //身份证号码
    //$params['cardNo']=$sfzid;
    //身份证姓名
    //$params['realName']=$skxx_khm;
    public function APISTORE_POST($params=[])
    {
        $url='http://s01.market.alicloudapi.com';
        $account=cc('account','verified');
        $appCode = $account['appcode'];
         
        $host = $url;
        $path = "/idCradTow";
        $method = "POST";
        $appcode = $appCode;
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
        $querys = "";
        $bodys = "{\"idNumber\":\"".$params['cardNo']."\",\"name\":\"".$params['realName']."\",\"serviceCode\":\"X01\"}";
        
        $url = $host . $path;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
       
        $callbcak = curl_exec($curl);
        //http状态码
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);

        //阿里云http统一状态码,请参阅 https://help.aliyun.com/document_detail/43906.html
        if (in_array($httpCode,array(400,403)))
            getjoson(0,"网络错误，请联系管理员","");
        //关闭,释放资源
        curl_close($curl);
        //返回内容JSON_DECODE
        $res=json_decode($callbcak, true);
        if ($res['code'] == 200) {
            if($res['data']['key']!='0000'){
                error('认证失败',400);
            }
        }else{
            error('认证失败',400);
        }
        return true;
    }

}