<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 查询余额
 */

namespace extend\block;
use xtype\Ethereum\Client as EthereumClient;
use xtype\Ethereum\Utils;

class ETHBances{
	public function __construct(){
        if (!defined('JSAPI_ROOT')) {
			define('JSAPI_ROOT', dirname(__FILE__) . '/');
			require_once(JSAPI_ROOT . 'vendor/autoload.php');
			require_once(JSAPI_ROOT . 'Lib/Keccak.php');
			require_once(JSAPI_ROOT . 'Lib/Tool.php');
		}
	}

	public function index($publickey)
	{

        $client = new EthereumClient([
            'base_uri' => 'https://mainnet.infura.io/v3/f4ab919e33ee4cc58d3996ed1e68f133',
            'timeout' => 30,
        ]);
       
        $nv = $client->eth_getBalance($publickey,'latest');

        //print_r('step_2:'.$nv);
        //echo "<br>";

        $nv_we = Utils::hexToDec($nv);
        //print_r('step_3:'.$nv_we); //最小单位 wei 1个eth相当于10的18次方wei
        //echo "<br>";

        $s = Utils::weiToEth($nv_we,false); 
        //print_r('ETH余额:'.$s);
        //echo "<br>";
        return $this->sctonum($s);
    }

    public function sctonum($num, $double = 8){
        if(false !== stripos($num, "e")){
            $a = explode("e",strtolower($num));
            return bcmul($a[0], bcpow(10, $a[1], $double), $double);
        }else{
            return $num;
        }
    }
    
}


