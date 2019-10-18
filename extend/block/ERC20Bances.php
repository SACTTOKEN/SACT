<?php



require_once "vendor/autoload.php";
ini_set("display_errors", "On");

error_reporting(E_ALL | E_STRICT);
use xtype\Ethereum\Client as EthereumClient;
use xtype\Ethereum\Utils;

//网络连接
$net='https://mainnet.infura.io/v3/f4ab919e33ee4cc58d3996ed1e68f133';

$client = new EthereumClient([
    'base_uri' => $net,
    'timeout' => 30,
]);
echo "<pre>";
$todaress='0xc2b76698974f63969d972a710c552a54f82e7ba2'; 
$todaress=Utils::remove0x($todaress);
$contract='0xdac17f958d2ee523a2206206994597c13d831ec7';
$data='0x70a08231000000000000000000000000'.$todaress;
$cv = $client->eth_call(
    [
        'to'=>$contract,
        'data'=>$data
    ],'latest');
echo "<br>";

print_r($cv);
$nv_we = substr($cv, 44);
echo "<br>";
echo $nv_we;

$nv_we='0x'.$nv_we;

echo "<br>";
print_r($nv_we);
echo "<br>";
$s = Utils::weiToEth($nv_we,false);
print_r('代币余额:'.$s);
echo "<br>";

