<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-16 15:33:08
 * Desc: 快递鸟接口
 */
namespace app\service;

class kdn{
    public $EBusinessID; //电商ID
    public $AppKey;
    public $ReqURL;

    public function __construct()
    {
      $config_M=new \app\model\config();
        $kdn_id = $config_M->find('kdn_id');
        $kdn_key =  $config_M->find('kdn_key');
        $this->EBusinessID = $kdn_id;
        $this->AppKey =  $kdn_key;
        $this->ReqURL = "http://api.kdniao.com/api/Eorderservice";
    }

    public function ship($id)
    {
        $orderM=new \app\model\order();
        $ar = $orderM->find($id);
        $orderM->up($id,['mail_type'=>1]);
        if($ar['status']=='未支付' || $ar['status']=='已关闭'){
            return '未支付';	
        }
        if($ar['mail_oid']!=''){
            return '已发货';	
        }
        $sid = $ar['sid'];
        $mail_M = new \app\model\mail();
        $where['sid']=$sid;
        $rs = $mail_M->have($where);

        $data['title_en'] = $rs['kdn_shipper_code']; //合作快递编码 如:SF
        $data['oid']      = $ar['oid'];

        $data['sender_name']     =  $rs['kdn_sender'];
        $data['sender_mobile']   =  $rs['kdn_sender_mobile'];       
        $data['sender_province'] =  $rs['kdn_sender_province']; 

        $data['sender_city']    =  $rs['kdn_sender_city'];    
        $data['sender_area']    =  $rs['kdn_sender_area'];   
        $data['sender_address'] =  $rs['kdn_sender_address'];      

        $data['mail_name']     = $ar['mail_name'];
        $data['mail_tel']      = $ar['mail_tel'];
        $data['mail_province'] = $ar['mail_province'];
        $data['mail_city']     = $ar['mail_city'];
        $data['mail_area']     = $ar['mail_area'];
        $data['mail_address']  = $ar['mail_town'].$ar['mail_address'];

        $search = array('&','%','<','>','*','+','\\',"'",'"',"#");
        $data['mail_address']=str_replace($search,' ',$data['mail_address']);


        $order_product_M = new \app\model\order_product();
        $op_ar = $order_product_M->find_by_oid($ar['oid']);
        $i=1;

        //$product_M = new \app\model\product();
        foreach($op_ar as $one){
            if($i==1){$data['goods_name'] = $data['goods_name'] . $one['title'].'X'.$one['number'];} //多个商品只填一个
            $i++;
            //$goods_info .=  $one['title'].'X'.$one['number'].' ';
            //$sub_title = $product_M->find($one['pid'],'sub_title');
            //$goods_info .=  mb_substr($sub_title,0,12).'...X'.$one['number'].' ';
        }	
        $data['goods_name'] = $data['goods_name'];
        $search = array('&','%','<','>','*','+','\\',"'",'"',"#");
        $data['goods_name']=str_replace($search,' ',$data['goods_name']);
        $back =	$this->kdn_order($data);
        if(isset($back['mail_oid'])){
            $data_up['mail_courier'] = $rs['kdn_shipper']; 
            $data_up['mail_id'] = $rs['id'];   //发货商家 物流id，mail表ID
            $data_up['mail_oid'] = $back['mail_oid'];
            $data_up['kdn_order_code'] = $back['kdn_order_code'];
            $data_up['print_template'] = $back['print_template'];
            $data_up['mail_time'] = time();
            
            $order_ar = $orderM->find($id);
            if($data_up['mail_oid']!='' && $order_ar['status']=='已支付'){
            $data_up['status'] = '已发货';
            }
            $res=$orderM->up($id,$data_up);
            if(empty($res)){
                return '提交失败';
            }
          return true;
        }else{
         	return $back;
        }
    }

    /*电子面单*/
	public function kdn_order($data){	
        //构造电子面单提交信息
        $eorder = [];

        $mail_M = new \app\model\mail();

        $where['sid']=0;
        $rs = $mail_M->have($where);


        $eorder["CustomerName"] =  $rs['kdn_customer_name'];         //'215870_0015';
        $eorder["CustomerPwd"] = $rs['kdn_customer_pwd'];           //'Xjfvk3jEVUaJ';
        $eorder["ShipperCode"] =  $rs['kdn_shipper_code'];         //快递公司编码  
        $eorder["OrderCode"] = $data['oid']; //订单编号(自定义，不可重复)
        $eorder["PayType"] = 1; //邮费支付方式:1-现付，2-到付，3-月结，4-第三方支付(仅SF支持)
        $eorder["ExpType"] = 1; //快递类型：1-标准快件 //快递类型 1次日达 2 隔日达

        $sender = [];
        $sender["Name"] = $rs['kdn_sender_name'];                       //发件人
        $sender["Mobile"] = $rs['kdn_sender_mobile'];                  //发件人电话   
        $sender["ProvinceName"] = $rs['kdn_sender_province'];         //发件省
        $sender["CityName"] = $rs['kdn_sender_city'];                //发件市
        $sender["ExpAreaName"] = $rs['kdn_sender_area'];            //发件区
        $sender["Address"] = $rs['kdn_sender_address'];            //发件人详细地址

        $receiver = [];
        $receiver["Name"] = $data['mail_name'];
        $receiver["Mobile"] = $data['mail_tel'];
        $receiver["ProvinceName"] =  $data['mail_province'];
        $receiver["CityName"] = $data['mail_city'];
        $receiver["ExpAreaName"] =  $data['mail_area'];
        $receiver["Address"] = $data['mail_address'];

        $commodityOne = [];
        $commodityOne["GoodsName"] = $data['goods_name'];
        $commodity = [];
        $commodity[] = $commodityOne;

        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;

        $eorder["IsReturnPrintTemplate"] = 1; //返回电子面单模板：0-不需要；1-需要
        $eorder["TemplateSize"] = 130;  //130 二联，180三联



            
        //调用电子面单
        $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);
        //提交
        $jsonResult = $this->submitEOrder($jsonParam);

        $result = json_decode($jsonResult, true);

        if($result["ResultCode"] == "100"  || $result["ResultCode"] == "106") {
            $data['mail_oid'] = $result['Order']['LogisticCode'];
            $data['kdn_order_code'] = $result['Order']['KDNOrderCode'];
            $create_S = new \app\service\create_html();
            $url=$create_S->save_content($result['PrintTemplate']);
            $data['print_template'] = $url;
            return $data;
        }else {
            return $result['Reason'];
        }
        
	}


    function submitEOrder($requestData){
    $datas = array(
        'EBusinessID' => $this->EBusinessID,
        'RequestType' => '1007',
        'RequestData' => urlencode($requestData) ,
        'DataType' => '2',
    );
    $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
    $result=$this->sendPost($this->ReqURL, $datas);   
    
    //根据公司业务处理返回的信息......
    
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