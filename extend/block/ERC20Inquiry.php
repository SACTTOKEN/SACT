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

class ERC20Inquiry
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

    public function index($publickey)
    {

        $config_M=new \app\model\config();
        $recharge_M=new \app\model\recharge();
        $money_S=new \app\service\money();
        $client = new EthereumClient([
            'base_uri' => 'https://mainnet.infura.io/v3/f4ab919e33ee4cc58d3996ed1e68f133',
            'timeout' => 30,
        ]);
        //--  查询出需要监测的会员地址
        //$dataadress=db('eth_from')->select();
        //--  查询上次监测的区块高度
        //$sql="SELECT MAX(number) AS maxnum FROM fa_eth_block";
        //$maxnum=db('eth_block')->query($sql);
        //$maxnum=$maxnum[0]['maxnum']+1;
        
        //--获取链上最新的区块高度
        $maxblock = Utils::hexToDec($client->eth_blockNumber());

        $maxnum=$config_M->find('dapp_maxnum');
        if(empty($maxnum)){
            $maxnum=$maxblock;
        }
        $config_M->up('dapp_maxnum',['value'=>$maxblock]);

        //var_dump($maxblock);
        //dump($maxblock);
       // return;
        //--遍历最新的区块
        for ($i = $maxnum; $i < $maxblock; $i++) {
            // dump('开始遍历');
            //获取最新的区块数据

            $blockdata = $client->eth_getBlockByNumber(Utils::decToHex($i), true);
            //--数据转换
           
            $blockdata = $this->object_to_array($blockdata);
            //$blockdata = $arr;
            //--获取所有的交易哈希
            $transarr = $blockdata['transactions'];
            $times=Utils::hexToDec($blockdata['timestamp']);
            //--查询到区块高度存入数据库
            // db('eth_block')->insert([
            //     'number'=>$i
            // ]);
			//echo $i;
			//-- 获取所有的要监控的eth地址数组长度
            //$num = count($dataadress);
            //--  查询区块中的的子交易
            if (!empty($transarr)) {
                for ($j = 0; $j < count($transarr); $j++) {
                    $res1 = $this->object_to_array($transarr[$j]);
                    //--获取配置信息
                  /*  if($dataadress) {
                        for ($k = 0; $k < $num; $k++) {*/
                            //$adress = $dataadress[$k]['fromadress'];
                            //-- 与数据库中的收款地址匹配
                            $res1['to']='0x'.preg_replace('/^0+/','',substr($res1['input'],10,64));
                            if($res1['to']==$publickey){
                        	$money=preg_replace('/^0+/','',substr($res1['input'],74));
                        	$money=Utils::hexToDec($money) / pow(10,6);
                            	//--匹配成功说明有用户充值
                            	//-- 发送地址   $res1['from']
                            	//-- 接收地址   $res1['to']
                            	//-- 发送金额   round(Utils::weiToEth(Utils::hexToDec($res1['value']),false), 6)
                            	//-- 哈希值   $res1['hash']
                               
                                //这里可执行数据库操作，给会员增加账户金额
                                $where=[];
                                $where['imtoken']=$res1['from'];
                                $where['money']= $money;
                                $where['status']= 2;
                                $recharge_ar=$recharge_M->have($where);
                                if($recharge_ar){
                                    $data=[];
                                    $data['hash']=$res1['hash'];
                                    $data['status']=1;
                                    $recharge_M->up($recharge_ar['id'],$data);
                                    $money_S->plus($recharge_ar['uid'], $recharge_ar['money'], 'USDT', "dapp_recharge", $recharge_ar['oid'], $recharge_ar['uid'],$res1['hash'],'');
                                }
                            }
                    /*    }
                    }*/
                }
            }
        }
    }


    /* 字符串长度 ‘0’左补齐
    * @param string $str   原始字符串
    * @param int $bit      字符串总长度
    * @return string       真实字符串
    */
    public function fill0($str, $bit = 64)
    {
        if (!strlen($str)) return "";
        $str_len = strlen($str);
        $zero = '';
        for ($i = $str_len; $i < $bit; $i++) {
            $zero .= "0";
        }
        $real_str = $zero . $str;
        return $real_str;
    }

    /**
     * 对象转换数组
     */
    public  function object_to_array($obj)
    {
        if (is_array($obj)) {
            return $obj;
        }
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        if (!is_array($_arr)) {
            cs($_arr,1);
        }
        foreach ($_arr as $key => $val) {
            $val = (is_array($val)) || is_object($val) ? $this->object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }


    /**
     * 把对象转化为json
     */
    public  function object_to_json($obj)
    {
        $arr2 = $this->object_to_array($obj); //先把对象转化为数组
        return json_encode($arr2);
    }
}
