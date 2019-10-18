<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: zb接口
 */
namespace app\ctrl\common;
use app\model\coin_currency as coin_currency_Model;

class zb{
    public $coin_currency_M;
    public $usdt_price_cny;
    public function __construct(){
		$this->coin_currency_M=new coin_currency_Model();
        $this->usdt_price_cny=$this->coin_currency_M->find("USDT","price_cny");
    }
    
	public function index(){
		$this->gxbtc('usdt_qc','USDT');
        //$this->gxbtc('btc_usdt','BTC');
        $this->gxbtc('eth_usdt','ETH');
        //$this->gxbtc('ltc_usdt','LTC');
        //$this->gxbtc('etc_usdt','BCH');
        return true;
	}
    
    public function gxbtc($symbols,$iden)
    {
        $result=$this->Hangqing($symbols);
		if(!in_array('error',$result)){
			if(is_array($result)){
				if($iden=="USDT"){
                    $data['price_cny']=sprintf("%.4f",$result['ticker']['last']);
                    $data['price_usdt']=1;
                    $this->coin_currency_M->up($iden,$data);
				}else{
                    $data['price_cny']=sprintf("%.4f",$result['ticker']['last']*$this->usdt_price_cny);
                    $data['price_usdt']=$result['ticker']['last'];
                    $this->coin_currency_M->up($iden,$data);
				}
			}
		}
    }
    
    public function Hangqing($symbols){
        $Url_btc="http://api.zb.com/data/v1/ticker?market=".$symbols;
        $res=array();
        $res=$this->HangqingRequest($Url_btc);
        return $res;
    }

    public function HangqingRequest($pUrl){
        $tCh = curl_init();
        curl_setopt($tCh, CURLOPT_URL, $pUrl);
        curl_setopt($tCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($tCh, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($tCh, CURLOPT_TIMEOUT, 1);
        $tResult = curl_exec($tCh);
        curl_close($tCh);
        $tResult=json_decode ($tResult,true);
        return $tResult;
    }
}