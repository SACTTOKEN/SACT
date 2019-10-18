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

class ETHSend
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

    public function index($withdraw_ar)
    {
        $client = new EthereumClient([
            'base_uri' => 'https://mainnet.infura.io/v3/f4ab919e33ee4cc58d3996ed1e68f133',
            'timeout' => 30,
        ]);
        $client->addPrivateKeys([$withdraw_ar['privatekey']]);
        // 2. 建立您的交易
        $trans = [
            "from" => $withdraw_ar['publickey'],
            "to" => $withdraw_ar['transferkey'],
            "value" => Utils::ethToWei($withdraw_ar['money'], true),
            "data" => '0x',
        ];
        // 你可以设定汽油手续费，nonce，gasprice
        $trans['gas'] = dechex(hexdec($client->eth_estimateGas($trans)) * 1.5);
        $trans['gasPrice'] = $client->eth_gasPrice();
        //$trans['gasPrice'] = Utils::decToHex('25000000000');
        $trans['nonce'] = $client->eth_getTransactionCount($withdraw_ar['publickey'], 'pending'); ////发送方  --生成钱包的公钥
        // 3. 发送您的交易
        // 如果需要服务器，也可以使用eth_sendTransaction
        $txid = $client->sendTransaction($trans);
        $client->eth_getTransactionReceipt($txid);
        return $txid;
    }


    /**	
     *  获取发送地址最新的Nonce	
     */
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
            "to" => $withdraw_ar['transferkey'],
            "value" => Utils::ethToWei($withdraw_ar['money'], true),
            "data" => '0x',
        ];
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
