<?php

/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 虚拟币转币
 */

namespace extend\block;

use xtype\Ethereum\Client as EthereumClient;
use xtype\Ethereum\Utils;
use kornrunner\Keccak;
use Lib\Tool;

class ERC20Send
{
    public function __construct()
    {
        if (!defined('JSAPI_ROOT')) {
            define('JSAPI_ROOT', dirname(__FILE__) . '/');
            require_once(JSAPI_ROOT . 'vendor/autoload.php');
            require_once(JSAPI_ROOT . 'Lib/Keccak.php');
            require_once(JSAPI_ROOT . 'Lib/Tool.php');
        }
    }

    
    public function gatNonce($withdraw_ar)
    {
        $client = new EthereumClient([
            'base_uri' => 'https://mainnet.infura.io/v3/f4ab919e33ee4cc58d3996ed1e68f133',
            'timeout' => 30,
        ]);
        $client->addPrivateKeys([$withdraw_ar['privatekey']]);

        $nonce = $client->eth_getTransactionCount($withdraw_ar['publickey'], 'pending');
        return hexdec($nonce);
    }

    public function indexByNonce($withdraw_ar, $nonce)
    {
        $client = new EthereumClient([
            'base_uri' => 'https://mainnet.infura.io/v3/f4ab919e33ee4cc58d3996ed1e68f133',
            'timeout' => 30,
        ]);
        $client->addPrivateKeys([$withdraw_ar['privatekey']]);
        // 2. 建立您的交易
        $trans = [
            "from" => $withdraw_ar['publickey'],
            //"to" => $withdraw_ar['transferkey'],
            "to" => '0xdac17f958d2ee523a2206206994597c13d831ec7',
            "value" => '0x0',
            //"data" => '0x',
        ];

        
        //tranfer的abi名称
        $str = "transfer(address,uint256)";
        //SHA-3，之前名为Keccak算法，是一个加密杂凑算法。
        $hash = Keccak::hash("transfer(address,uint256)",256);
        $hash_sub = mb_substr($hash,0,8,'utf-8');
        //接收地址
        $fill_from = Tool::fill0(Utils::remove0x($withdraw_ar['transferkey']));
        //转账金额
        //$num10 = Utils::ethToWei($num);
        $num10=$withdraw_ar['money'] * 1000000;
        $num16 = Utils::decToHex($num10);
        $fill_num16 = Tool::fill0(Utils::remove0x($num16));

        //开始拼接
        $trans['data'] = "0x" . $hash_sub . $fill_from . $fill_num16;


        // 你可以设定汽油手续费，nonce，gasprice
        $trans['gas'] = dechex(hexdec($client->eth_estimateGas($trans)) * 1.5);
        //$trans['gasPrice'] = $client->eth_gasPrice();
        $trans['gasPrice'] = Utils::decToHex('25000000000');
        //$trans['nonce'] = $client->eth_getTransactionCount($withdraw_ar['publickey'], 'pending');////发送方  --生成钱包的公钥
        //cs($nonce);
        //cs(dechex($nonce));
        $trans['nonce'] = Utils::decToHex($nonce); ////发送方  --生成钱包的公钥
        //cs($trans,1);
        // 3. 发送您的交易
        // 如果需要服务器，也可以使用eth_sendTransaction
        $txid = $client->sendTransaction($trans);
        $client->eth_getTransactionReceipt($txid);
        return $txid;
    }
}
