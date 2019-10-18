<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: cs接口
 */

namespace app\ctrl\common;
use core\lib\redis;
use core\lib\Model;
use app\model\news as NewsModel;
use app\model\news_cate as NewsCateModel;

class cs{
    public $redis;
    public $Model;
	public $newsM;
	public $news_cate_M;
    public $user_M;
    public $user_attach_M;
    public $user_gx_M;
    public $rating_M;
    public $money_S;
    public $orderM;
    public function __construct(){
        $this->redis = new redis();
        $this->Model = new Model();
        $this->user_M = new \app\model\user();
        $this->redis = new redis();
        $this->user_attach_M = new \app\model\user_attach();
        $this->user_gx_M = new \app\model\user_gx();
        $this->rating_M = new \app\model\rating();
        $this->money_S = new \app\service\money();
        $this->orderM = new \app\model\order();
    }


    public function abc(){
        //echo "666";
        $tel_str = trim(post('aaa'));            
        //var_dump(htmlspecialchars($tel_str));
        //$tel_ar =  str_replace("\r\n",",",$tel_str);
        //$tel_ar = explode(',',$tel_ar);
        //$tel_ar = preg_replace('//s*/', '', $tel_str);
        $tel_ar = preg_replace('/[\n\r\t]/', '@', $tel_str);

        cs($tel_ar,1);
      exit();
    }

    public function sum(int ...$ints)
    {
       return array_sum($ints);
    }


    //确认收货
	public function confirm()
	{
		$id=4;
		$redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
		$redis->multi();
		$this->orderM->up($id,['is_settle'=>0]);
		(new \app\service\order_complete())->complete($id);
		cs($this->orderM->log(),1);
    }
    
    public function dapp()
    {
        exit;
        $data=(new \app\model\dapp_order())->lists(['status'=>1]);
        foreach($data as $vo){
            $product_ar=(new \app\model\dapp_product())->find($vo['pid']);
            $data2['income']=$vo['money']+$vo['money']*$product_ar['income']/1000;
            $data2['income_time']=$vo['update_time']+$product_ar['day']*86400;
            (new \app\model\dapp_order())->up($vo['id'],$data2);
        }
    }

    public function tt(){
        $wx_S = new \app\service\wechat();
        $res = $wx_S->service_msg();
        cs($res,1);     
    }


    //团队奖+平级奖
    public function team_award()
    {
        $redis = new \core\lib\redis();
$Model = new \core\lib\Model();
$Model->action();
$redis->multi();
        $order_ar=(new \app\model\order())->find(148);
        $y_where['uid'] = $order_ar['uid'];
        $y_user_gx_ar = $this->user_gx_M->lists_plus($y_where);
        $team = 0;
        foreach ($y_user_gx_ar as $vo) {
            $t_rating = $this->user_M->find($vo['tid'], 'rating');
            $bonus_ratio = $this->rating_M->find($t_rating, ['team', 'team_same', 'team_account']);
          
            if ($bonus_ratio['team'] > $team) {
                $reward = $order_ar['reward'] * ($bonus_ratio['team'] - $team) / 1000;
                if ($reward > 0) {
                    $sum = '';
                    if ($bonus_ratio['team_account'] == 'integral') {
                        $sum = 'sum_integral';
                    } elseif ($bonus_ratio['team_account'] == 'amount') {
                        $sum = 'sum_amount';
                    }
                    $this->money_S->plus($vo['tid'], $reward, $bonus_ratio['team_account'], 'team_award', $order_ar['oid'], $order_ar['uid'], '', $sum); //记录资金流水

                    //发放平级奖
                    if ($bonus_ratio['team_same'] > 0) {
                        $this->team_same($order_ar, $vo['tid'], $t_rating, $bonus_ratio['team_same'], $bonus_ratio['team_account']);
                    }
                	$team = $team + ($bonus_ratio['team'] - $team);
                }
            }
            $is_max = $this->rating_M->is_have(['id[>]' => $t_rating]);
            if (!$is_max) {
                return;
            }
        }
        cs($this->rating_M->log());
    }

    //平级奖
    public function team_same($order_ar, $tid, $t_rating, $team_same, $team_account)
    {
        $tid = $this->user_M->find($tid, 'tid');
        if (empty($tid)) {
            return;
        }
        $where['rating'] = $t_rating;
        $where['id'] = $tid;
        $users = $this->user_M->have($where, 'id');
        if ($users) {
            $reward = $order_ar['reward'] * $team_same / 1000;
            if ($reward > 0) {
                $sum = '';
                if ($team_account == 'integral') {
                    $sum = 'sum_integral';
                } elseif ($team_account == 'amount') {
                    $sum = 'sum_amount';
                }
                $this->money_S->plus($users, $reward, $team_account, 'team_same', $order_ar['oid'], $order_ar['uid'], '', $sum); //记录资金流水
            }
        }
    }


    public function test(){
        $web = "http://finance.eastmoney.com/a/cywjh.html";
        $file = file_get_contents($web);
        $preg_0 = "#<li id=\"newsTr0\">(.*)</li>#iUs";
        preg_match($preg_0,$file,$ar0); 
        $str = $ar0[1];
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern,$str,$match);
        $pic = $match[1][0]; //封面图
        $preg_1 = "#<a target=\"_blank\" href=\"(.*)\">#iUs";
        preg_match($preg_1,$str,$ar1); 
        $src = $ar1[1];           //链接  
        $this->eastmoney($src,$pic);
        exit();
    }

    public function eastmoney($src,$pic){
        if(empty($src)){return false;}
        $file = file_get_contents($src);
        $new_id = 0;
        $preg_2 = "#<h1>(.*)</h1>#iUs";
        preg_match($preg_2,$file,$ar2); 
        $title = $ar2[1]; //标题
        $preg_3 = "#<div id=\"ContentBody\" class=\"Body\">(.*)<p class=\"em_media\">#iUs";
        preg_match($preg_3,$file,$ar3); 
        $content = $ar3[1];
        $preg_4 = "#<p>(.*)</p>#iUs";
        preg_match_all($preg_4,$file,$ar4);
        $str = implode(' ',$ar4[0]);
        $str = preg_replace("/<a[^>]*>(.*?)<\/a>/is", "$1", $str); //内容 存sub_title
        $title = trim($title);
        $where['title'] = $title;

        $product_M = new \app\model\product();
        $is_have = $product_M->is_have($where);
        if(!$is_have){
            $data['title'] = $title;
            $data['sub_title'] = $str;
            $data['piclink'] = $pic;
            $data['cate_id'] = 77;
            $product_M->save($data);
        }
    }



    public function wxx(){
        return false;
        $money = 150;
        $openid =                               //'octLbwimNQup7H8NbhFVCQA1jLJI'; ad
        $sender = "YK";
        $mch_id = '1321942401';
        $appid = c('appid');
        $wishing = "恭喜发财";

        $obj = array();
        $obj['wxappid']         = $appid;
        $obj['mch_id']          = $mch_id;
        $obj['mch_billno']      = $mch_id.date('YmdHis').rand(1000, 9999);
        $obj['client_ip']       = $_SERVER['REMOTE_ADDR'];
        $obj['re_openid']       = $openid;
        $obj['total_amount']    = $money;
        $obj['total_num']       = 1;
        $obj['nick_name']       = $sender;
        $obj['send_name']       = $sender;
        $obj['wishing']         = $wishing;
        $obj['act_name']        = "";
        $obj['remark']          = "";
        

        $appid = renew_c('appid'); //'wxe392a6831e86dd02'; 
        $appsecret = renew_c('appsec');   //'1f8c40fb70e9ce4bbaebc8fa61100a0d';// 
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $wxHongBaoHelper = new \extend\wechat_cash\wx_redpack($appid,$appsecret);


        $data = $wxHongBaoHelper->wxpay($url, $obj, true);
        $res = $wxHongBaoHelper->xmlToArray($data);
       
        if($res['return_code']=='SUCCESS' && $res['result_code']!='FAIL'){
            return true;
        }else{
            return $res['err_code_des'];
        }
    }










    //安居客列表页采集
    public function ajk(){

        $web = "https://fz.anjuke.com/sale/p1/";
        $file = file_get_contents($web);
// <a data-from="" data-company="" title="东二环泰禾旁，08年商品房社区，电梯中层钢需两房南北双阳台" href="https://fz.anjuke.com/prop/view/A1756546458?from=filter&amp;spread=commsearch_p&amp;uniqid=pc5d51192d4961b7.82468607&amp;position=1&amp;kwtype=filter&amp;now_time=1565595949" target="_blank" class="houseListTitle">
       
        $preg_0 = "#<div class=\"house-title\">(.*)</div>#iUs";
        preg_match_all($preg_0,$file,$ar0);
        $product_M = new \app\model\product();

        $i=1;
        foreach($ar0[1] as $one){
            $str = trim($one);
            $preg_1 = "#<a data-from=\"\" data-company=\"\"  title=\"(.*)\" href=\"(.*)\"#iUs";
            preg_match($preg_1,$str,$ar1);
            $web_link = $ar1[2];
            $is_have = $product_M->is_have(['ajk_link'=>$web_link]);
            if($is_have){
                continue;
            }
         
            if($i<3){
            echo $i.$ar1[1];
            echo "<br>";
            $this->ajk_info($web_link);
            }

            $i++;
        }

        exit();





    }


    //安居客详情页采集
    public function ajk_info($web_link){
        //$web_link = "https://fz.anjuke.com/prop/view/A1784363681?from=filter&spread=commsearch_p&uniqid=pc5d50e2e66075d9.93519089&position=61&kwtype=filter&now_time=1565582054"; 
        // $web_link = "https://fz.anjuke.com/prop/view/A1799437079?from=filter&spread=commsearch_p&uniqid=pc5d50e2e6607a71.70148173&position=63&kwtype=filter&now_time=1565582054";
        if(empty($web_link)){return false;};
        $file = file_get_contents($web_link);

        $new_id = 0;
        $preg_3 = "#<h3 class=\"long-title\">(.*)</h3>#iUs";
        preg_match($preg_3,$file,$ar3);
        $title = $ar3[1];
        $preg_4 = "#<div class=\"houseInfo-item-desc js-house-explain\">(.*)</div>#iUs";
        preg_match($preg_4,$file,$ar4);
        $preg_5 = "#<span style=\"font-size:14px;\">(.*)</span>#iUs";
        preg_match($preg_5,$ar4[1],$ar5);
        $content = $ar5[1];
        
        $data['ajk_link'] = $web_link;
        $data['title'] = trim($title);
        $data['content'] = $content;
        $data['cate_id'] = 75; //房产
        $where['title'] = $title;
        $product_M = new \app\model\product();
        $is_have = $product_M->is_have($where);
        if(!$is_have){
            $new_id = $product_M->save($data);
        }

        if($new_id>0){
            $sku_ar = array();
            $sku_ar['price'] = 0;
            $sku_ar['cost_price'] = 0;
            $sku_ar['stock'] = '999';
            $sku_ar['pid'] = $new_id;
            $sku_res = (new \app\model\product_sku())->save($sku_ar);

            $data2['aid'] = $new_id;
            $data2['cate'] = 'product';
            $preg_1 = "#<div class=\"img_wrap\">(.*)</div>#iUs";
            preg_match_all($preg_1,$file,$ar1);
            if(empty($ar1)){return;}
            $img_ar =$ar1[1];

            $preg_2 = "#<img data-src=\"(.*)\" src=\"(.*)\" alt=\"\" height=\"450\">#iUs";

            $image_M = new \app\model\image();
            $i=1;
            foreach($img_ar as $img_one){
                $piclink_1 = '';
                if($img_one){
                    preg_match($preg_2,trim($img_one),$ar2);
                    if(isset($ar2[1])){
                        $data2['piclink'] = $ar2[1];
                        $image_M->save($data2);

                        if($i==1){
                            $piclink_1 = $ar2[1];
                            $product_M->up($new_id,['piclink'=>$piclink_1]);
                        }

                    }
                $i++;    
                }
            }

        }

        // echo $new_id;
        // exit();
    }



    public function huafei(){

        $recharge = new \app\service\juhe_huafei();

        $telCheckRes = $recharge->telcheck('18650071918',100); //boolean

        var_dump($telCheckRes);



        if($telCheckRes){
        //说明支持充值，可以继续充值操作，以下可以根据实际需求修改
        //echo "OK";
            $res = $recharge->telquery('18650071918',100);
            // var_dump($res);
            // array (size=4)
            // 'cardid' => string '11002' (length=5)
            // 'cardname' => string '福建联通话费1元' (length=22)
            // 'inprice' => float 1.06
            // 'game_area' => string '福建福州联通' (length=18)     

            // $oid = date('Ymdhis').rand(10000,99999);
            // $res = $recharge->telcz('18650071918',1,$oid);

            var_dump($res);

            exit();

        // $res =  [
        //         'reason' =>'订单提交成功，等待充值',
        //         'result' => [
        //             'cardid' => '11002',
        //             'cardnum' => '1',
        //             'ordercash' => '1.06',
        //             'cardname' => '福建联通话费1元',
        //             'sporder_id' => 'J19080920293657729375999', //聚合订单号
        //             'uorderid' => '2019080908293026342',
        //             'game_userid' => '18650071918',
        //             'game_state' => '0', /*充值状态:0充值中 1成功 9撤销，刚提交都返回0*/
        //             ],
        //         'error_code' => 0, 
        //         ];

            exit();
        }else{
        //暂不支持充值，以下可以根据实际需求修改
            exit("对不起，该面额暂不支持充值");
        }
    }


    public function huafei_check(){

        $recharge = new \app\service\juhe_recharge();

        $res = $recharge->sta('2019080908293026342');

        echo "<pre>";
    // $res = [
    // 'reason' => '查询成功',
    // 'result' => 
    //     [
    //         'uordercash' => 1.060,
    //         'sporder_id' => 'J19080920293657729375999',
    //         'game_state' => 1,
    //     ],
    // 'error_code' => 0
    // ];
        exit();
    }    




    public function liuliang(){
        $recharge = new \app\service\juhe_liuliang();
        $telCheckRes = $recharge->telcheck('13696876143'); //boolean
        echo "<pre>";
        cs($telCheckRes);
        if($telCheckRes['error_code']==0){
            return $telCheckRes['result'][0];
        }


//         Array
// (
//     [reason] => success
//     [result] => Array
//         (
//             [0] => Array
//                 (
//                     [city] => 全国
//                     [company] => 中国联通
//                     [companytype] => 1
//                     [name] => 中国联通全国流量套餐
//                     [type] => 1
//                     [flows] => Array
//                         (
//                             [0] => Array
//                                 (
//                                     [id] => 34
//                                     [p] => 20M
//                                     [v] => 20
//                                     [inprice] => 2.880
//                                 )

//                             [1] => Array
//                                 (
//                                     [id] => 1
//                                     [p] => 50M
//                                     [v] => 50
//                                     [inprice] => 5.760
//                                 )

//                             [2] => Array
//                                 (
//                                     [id] => 35
//                                     [p] => 100M
//                                     [v] => 100
//                                     [inprice] => 4.800
//                                 )

//                             [3] => Array
//                                 (
//                                     [id] => 2
//                                     [p] => 200M
//                                     [v] => 200
//                                     [inprice] => 7.680
//                                 )

//                             [4] => Array
//                                 (
//                                     [id] => 36
//                                     [p] => 500M
//                                     [v] => 500
//                                     [inprice] => 14.400
//                                 )

//                             [5] => Array
//                                 (
//                                     [id] => 37
//                                     [p] => 1G
//                                     [v] => 1024
//                                     [inprice] => 19.200
//                                 )

//                         )

//                 )

//         )

//     [error_code] => 0
// )
        exit();
    }


    public function liuliang_in(){
        $recharge = new \app\service\juhe_liuliang();
        $oid = date('Ymdhis').rand(10000,99999);
        $res = $recharge->telcz('13696876143',3,$oid);

        echo "<pre>";
        cs($res);
        exit();

        //Array
        // (
        //     [reason] => 订单提交成功，请等待充值
        //     [result] => Array
        //         (
        //             [ordercash] => 2.985
        //             [cardname] => 中国移动全国流量套餐10M
        //             [sporder_id] => F19080922132672716363869
        //             [orderid] => 2019080910132381660
        //             [phone] => 13696876143
        //         )
        //     [error_code] => 0
        // )
    }


    public function liuliang_check(){

        $recharge = new \app\service\juhe_liuliang();

        $res = $recharge->sta('2019080910132381660');

        echo "<pre>";
        cs($res);
        //  <pre>Array
        // (
        //     [reason] => 查询成功
        //     [result] => Array
        //         (
        //             [uordercash] => 2.985
        //             [sporder_id] => F19080922132672716363869
        //             [game_state] => 1
        //         )
        //     [error_code] => 0
        // )
        exit();
    }    








    public function big_video(){
        $post = $_POST;
        $file = $_FILES;
        //$guid = post('guid');       //前端传来的GUID号    
       // $chunk = post('chunk');    //当前分块序号
       // $fileName = post('name'); 

        $dir = IMOOC."public/upload/".$post['guid'];
        if (!file_exists($dir)) mkdir ($dir,0777,true);

        var_dump($file);
      // 'name' => string '4ec79ca3fb716f1eed4cd6cc1fc4e978.mp4' (length=36)
      // 'type' => string 'video/mp4' (length=9)
      // 'tmp_name' => string '/tmp/phpEFpmEu' (length=14)
      // 'error' => int 0
      // 'size' => int 1697264
        
        // 移入缓存文件保存     
        $mp4 = date('Ymdhis').rand(10000,99999).".mp4";
        $res = move_uploaded_file($file["file"]["tmp_name"], $dir.'/'.$mp4);

        return $post['guid'].'@@'.$post['chunk'];
        
    }




    public function week(){
        $drag_M = new \app\model\drag();
        $where['sid']=10;
        $ar = $drag_M->lists_all($where);
        var_dump($ar);
        exit();
       
    }
    


    public function rating()
    {

        $uid=703;
        $this->mall($uid);

    }


    /*商城等级*/
    public function mall($uid)
    {
        //定制结算
        $user=$this->user_M->find_me($uid);
        $made=(new \app\model\config())->find('made');
        if($made){
			$ctrlfile=MADE.'/'.$made.'/service/rating.php';
            if(is_file($ctrlfile)){
                $cltrlClass='\made\\'.$made.'\\service\rating';
                $made_S=new $cltrlClass();
                $coin_rating=$made_S->mall($uid);
                if(!isset($coin_rating['id'])){
                    return;
                }
            }else{
                return;
            }
        }else{
            $coin_rating_M = new \app\model\rating();
            $where['id[>]']=$user['rating'];
            $where['zt_num[<=]']=$user['yvip'];
            $where['td_num[<=]']=$user['zvip'];
            $where['shop_buy[<=]']=$user['buy'];
            $where['assign_buy[<=]']=$user['buy_vip'];
            $where['recharge[<=]']=$user['sum_money'];
            $where['td_shop_buy[<=]']=$user['zsales'];
            $where['td_assign_buy[<=]']=$user['zsales_vip'];
            $coin_rating=$coin_rating_M->have($where);
            if(!$coin_rating){
                return;
            }
            //直推等级人数
            if($coin_rating['direct_rating']>0 && $coin_rating['direct_rating_number']>0){
                $dirwct_where['tid']=$uid;
                $dirwct_where['level']=1;
                $dirwct_where['rating[>=]']=$coin_rating['direct_rating'];
                $direct_rating_number=(new \app\model\user_gx())->new_count($dirwct_where);
                if($coin_rating['direct_rating_number']>$direct_rating_number){
                    return;
                }
            }
        }
        
        //升级
        renew_user_one($user['id'],['rating_cn'=>$coin_rating['title']]);
        $data['rating']=$coin_rating['id'];
        $this->user_M->up($user['id'],$data);
        $data2['upgrade_time']=time();
        if($user['rating']==1){
            $data2['vip_upgrade_time']=time();
        }
        $this->user_attach_M->up($user['id'],$data2);
        
        //统计分销人数
        if($user['rating']==1){
            $user_S = new \app\service\user();
            $user_S -> mall_rating_run($user['id'],$data['rating'],$user['rating']);
        }

        $user_gx_M = new \app\model\user_gx();
        $data_rating['rating']=$data['rating'];
        $user_gx_M->up($uid,$data_rating);

        $data_t_rating['t_rating']=$data['rating'];
        $user_gx_M->up_all(['tid'=>$uid],$data_t_rating);
        //判断上级等级
        if($user['rating']>1 && $coin_rating['direct_rating']>0 && $coin_rating['direct_rating_number']>0){
            if($user['tid']){
            $this->mall($user['tid']);
            }
        }
        //重新判断
        $this->mall($uid);
    }


    public function demo(){
 
        $created_time = 1563277665;
        $stake = 10.0000000;
        $win = 0.33;
        $cycle = 7;

        $coin_all = $stake * $win/100;

        $miao = ($stake * $win) / ($cycle * 24 * 3600 * 100);  //每秒涨币数
        $miao = number_format($miao, 8, '.', '');
        $coin_now = intval( time() - $created_time) * $miao;
        $coin_now  = number_format($coin_now, 8, '.', '');

        echo $miao;
        echo "<br>";
        echo $coin_now;
        echo "<br>";
        echo $coin_all;
        exit();

    }




    public function https_request($url, $data = null)
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




    public function bb(){

        $tel = '18650071918';
        $code = rand('100000','999999');
        $redis = new \core\lib\redis();
        $uni_code = uniqid(); //防验证码不是从请求来源过来的
        $value = $code."@".$uni_code;
        $redis->set("sms:".$tel,$value);
        $redis->expire("sms:".$tel,180);

        $redis_code = $redis -> get("sms:".$tel);


        echo $redis_code;
        exit();
    }




    public function aa(){
        $postObj= '{"ToUserName":"gh_b3a7c9e78cd4","FromUserName":"o0_V91b5416rdSz7kp0O6qzvoudA","CreateTime":"1561651141","MsgType":"event","Event":"subscribe","EventKey":[]}';
        $row = json_decode($postObj,true);

        if(isset($row['EventKey']) && !empty($row['EventKey'])){
            $tjm = str_replace("qrscene_",'',$row['EventKey']);
        }else{
            $tjm='';
        }
        $openid  = $row['FromUserName'];

        $power_C = new \app\ctrl\mobile\power();
        $power_C->__initialize();
        $aaa = $power_C->wx_reg($openid,$tjm);
        echo $aaa."AAA";

        exit();




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






    public function qq(){

        $str = '{"ToUserName":"gh_07521897d2bc","FromUserName":"o-JY05sZXucnfAXCJUSjxagK0Qyo","CreateTime":"1561547246","MsgType":"event","Event":"subscribe","EventKey":"qrscene_ak47ak","Ticket":"gQFB8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyal9BaUloVW1jUUMxMDAwMGcwN2IAAgRDNxNdAwQAAAAA"}';



        $ar = json_decode($str,true);

        $tjm = str_replace("qrscene_",'',$ar['EventKey']);
        $openid  = $ar['FromUserName'];

        $where['openid'] = $openid;
        $where['tjm'] = $tjm;
        $wx_openid_M = new \app\model\wx_openid();
        $is_have = $wx_openid_M->is_have($where);
        if(!$is_have){
            $data['tjm'] = $tjm;
            $data['openid'] = $openid;
            $res = $wx_openid_M->save($data);
        }

        return $res;
    }




    public function wx()
    {
       
        $wx=new \extend\wx\wechat();
        $wx->index();
    }

    public function tb_web(){
        //$web = "https://item.taobao.com/item.htm?spm=a219r.lm874.14.80.bd264961TNfQ5a&id=592023488090&ns=1&abbucket=18";
        $web = "https://item.taobao.com/item.htm?spm=a217f.8051907.312172.33.413933082WbFGp&id=574799861692";
        $file = file_get_contents($web);
        $preg_1 = "#<h3 class=\"tb-main-title\" data-title=\"(.*)\">#iUs";
        preg_match($preg_1,$file,$arr);
        if(empty($arr)){return;}
        $title = $arr[1];
        $title = strip_tags(iconv('GB2312', 'UTF-8', $title)); 
        // echo $title;
        // echo "<br>";        
        $preg_2 = "#auctionImages    : \[(.*)\]#iUs";
        preg_match($preg_2,$file,$arr2);
        $mpic = $arr2[1];
        // echo $mpic; //ok
        // echo "<br>";
        $preg_3 = "#descnew.taobao.com(.*)'#iUs";
        preg_match($preg_3,$file,$arr3);
        $desc_url = $arr3[1];
        $desc_url = "https://descnew.taobao.com".$desc_url;
        $file2 = file_get_contents($desc_url);
        $file2 = str_replace("var desc='", "", $file2);
        $file2 = mb_substr($file2,0,-3);
        $file2 = iconv('GB2312', 'UTF-8', $file2); 
        //echo $file2;

        //exit();
        
        $mpic_ar = explode(',',$mpic);
        $ar['title'] = $title;
        $ar['mpic'] = $mpic_ar;
        $ar['content'] = $file2;

        return $ar;
    }




    public function bug()
    {
        for($x=0; $x<10; $x++){
            $url='www.api.com/mobile/coin/saveadd';
            $data['id']='1';
            $data['sign']='312d0270fd932f702e512e6870bcb22e';
            $curl = curl_init();
            $headerArray =array("UID:14","X-FORWARDED-FOR:47.107.110.127","timestamp:1560245403","EXTRA:","MEID:","");

            $cookieVerify['user_token']='';
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_HTTPHEADER,$headerArray);
            curl_setopt($curl, CURLOPT_REFERER, 'http://admin.yxhshow.com/');//模拟来路
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieVerify);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
          
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            print_r($output);
        }
    }
    
  /*前端某一类新闻*/
	public function news_lists()
	{
		$this->newsM  = new NewsModel();
		$this->news_cate_M  = new NewsCateModel();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];			
		$cate_id = post('cate_id');
		if($cate_id){
			$where['cate_id'] = $cate_id;
		}	
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->newsM->lists($page,$page_size,$where);

	
        $res['data'] = $data; 
        return $res; 
	}
  
  	public function redisall(){
      $ar = (new \app\model\user())->lists_all();
      $i=0;
      $rating=new \app\model\coin_rating();
      foreach($ar as $one){
        $coin_rating_cn=$rating->find($one['coin_rating'],'title');
          renew_user_one($one['id'],['coin_rating_cn'=>$coin_rating_cn]);
          $i++;
      }       
      return $i;
    }
  
  public function gx_user_redis()
  {
    $user=(new \app\model\user())->lists_all([],'id');
    foreach($user as $vo){
      echo $vo;
    }
  }
  
	public function index(){
		$uid = 128;	
		$redis_name = 'user:'.$uid;
		$user = $this->redis->hget($redis_name);
    cs($user);
	}
    
	public function im(){
		$im = new \app\service\im();  
        $im->login_one(1000,'aaabbb');
	}
    
	public function user(){
		return (new \app\model\user())->find_all(1);
	}

    public function haojuan(){
    //step.1 采集        
    require_once(IMOOC."/extend/taobao/TopSdk.php");
    $c = new \TopClient;
    $c->appkey =  "26041159";
    $c->secretKey = "06fdc1cec3abd264ab14caf53d61a087"; 
    $req = new \TbkDgItemCouponGetRequest;
    $req->setAdzoneId('136154851');

    $req->setPlatform("1");
    $req->setCat("16,18");
    $req->setPageSize("5");
    //$req->setQ("女装");
    $req->setPageNo("1");
    $resp = $c->execute($req);
    $postObj = simplexml_load_string($resp, 'SimpleXMLElement', LIBXML_NOCDATA);
    $array = json_decode(json_encode($resp),true);
    $ar1 = object_to_array($array);
    $ar1=$ar1['results']['tbk_coupon'];

    //step.2 写入数据库，已存在更新，不存在添加
    $tb_goods_M = new \app\model\tb_goods();
    $data = [];
    if($ar1){
        foreach($ar1 as $one){
            $data = [
                'piclink'          => $one['pict_url'],
                'title'            => $one['title'],
                'nick'             => $one['nick'],
                'price'            => $one['zk_final_price'],
                'month_sale'       => $one['volume'], //30天销量
                'commission'       => $one['commission_rate'],//佣金比率(%)
                'seller'           => $one['nick'],
                'seller_shop'      => $one['shop_title'],
                'seller_platform'  => $one['user_type'] ? '商城' : '集市', //0表示集市，1表示商城
                'coupon_all'       => $one['coupon_total_count'],
                'coupon_left'      => $one['coupon_remain_count'],
                'coupon_info'      => $one['coupon_info'],
                'coupon_start_time'=> $one['coupon_start_time'],
                'coupon_end_time'  => $one['coupon_end_time'],
                'tbk_link'         => $one['item_url'],//商品链接
                'coupon_link'      => $one['coupon_click_url'],//商品优惠券推广链接
                'share_coupon_link'=> $one['coupon_click_url'],
                'num_iid'          => $one['num_iid'],
                'cate_id'          => '1',//好券清单
                ];
            $where['num_iid'] = $data['num_iid'];
            $is_have = $tb_goods_M->is_have($where);
            if($is_have){
                $res = $tb_goods_M ->up_all($where,$data);          
            }else{
                $res = $tb_goods_M ->save($data);      
            }   
        }
        empty($res) && error('采集失败',400);
    }
    return true;    
    }

    public function ip2addr(){
        $ip = '121.204.65.199';
        $address = ip_address($ip);   
        return $address;
    }


    public function send_wx_sms_demo(){
        $action = 'user_reg';
        $xid = 30; 
        $ar= [];
        mb_sms($action,$xid,$ar);
        return $xid;
    }

}