<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 创建钱包
 */

namespace extend\block;
use xtype\Ethereum\Client as EthereumClient;

class CreateEth{
	public function __construct(){
        if (!defined('JSAPI_ROOT')) {
			define('JSAPI_ROOT', dirname(__FILE__) . '/');
			require_once(JSAPI_ROOT . 'vendor/autoload.php');
			require_once(JSAPI_ROOT . 'Lib/Keccak.php');
			require_once(JSAPI_ROOT . 'Lib/Tool.php');
		}
	}

	public function index()
	{

        $client = new EthereumClient([
            //'base_uri' => 'https://rinkeby.infura.io/v3/a25b1f640f07417bbeed411fe7c5c8d7',
            'base_uri' => 'https://rinkeby.infura.io/v3/f4ab919e33ee4cc58d3996ed1e68f133',
            'timeout' => 30,
        ]);
        
        $res=$client->newAccount();
        return $res;
    }
}
