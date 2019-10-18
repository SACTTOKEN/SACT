<?php

/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 公用函数
 */
function cs($var, $types = 0)
{

    if (is_bool($var)) {
        var_dump($var);
    } else if (is_null($var)) {
        var_dump(NULL);
    } else {
        echo print_r($var, true);
    }
    if ($types == 1) {
        exit;
    }
}

function error($msg = '未知错误', $code = 404)
{
    throw new core\lib\Exception([
        'msg' => $msg,
        'code' => $code
    ]);
}

function post($name = '', $default = '')
{
    $date = core\lib\Request::instance()->post($name);
    if ($date == "") {
        $date = $default;
    }
    return $date;
}
function get($name = '')
{
    $date = core\lib\Request::instance()->get($name);
    return $date;
}
function cookie($name = '')
{
    $date = core\lib\Request::instance()->cookie($name);
    return $date;
}


/*获取客户端ip*/
function ip($type = 0)
{
    if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ip_ar = explode(",", $_SERVER['HTTP_X_REAL_IP']);
        if ($ip_ar[0]) {
            return $ip_ar[0];
        } else {
            return $ip_ar[1];
        }
    }
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown', $arr);
        if (false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}


/*手机访问*/
function isMobile()
{
    if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
        return true;
    } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
        return true;
    } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
        return true;
    } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
        return true;
    } else {
        return false;
    }
}


/*是否JSON数据*/
function is_json($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}




function i_array_column($input, $columnKey, $indexKey = null)
{
    if (!function_exists('array_column')) {
        $columnKeyIsNumber  = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull            = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber     = (is_numeric($indexKey)) ? true : false;
        $result                         = array();
        foreach ((array) $input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    } else {
        return array_column($input, $columnKey, $indexKey);
    }
}


function rsa_decrypt($str)
{
    define('IN_SYS', true);
    $de_str = \core\lib\RSAUtils::decrypt(trim($str));
    $new_time = substr($de_str, -10, 10);
    $new_de_str = substr($de_str, 0, -10);
    if ($new_time - time() > 1800 || $new_time - time() < -1800) {
        error("密码错误", 400);
    }
    $length = mb_strlen((string) $new_de_str);
    if ($length < 6) {
        error("密码最小6位", 400);
    }
    return $new_de_str;
}


function getRandChar($length = 32)
{
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;

    for (
        $i = 0;
        $i < $length;
        $i++
    ) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}

function getRandChar2($length = 32)
{
    $str = null;
    $strPol = "123456789abcdefghijkmnpqrstuvwxyz";
    $max = strlen($strPol) - 1;

    for (
        $i = 0;
        $i < $length;
        $i++
    ) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}

function getRandChar3($length = 32)
{
    $str = null;
    $strPol = "123456789";
    $max = strlen($strPol) - 1;

    for (
        $i = 0;
        $i < $length;
        $i++
    ) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}


/*读取config配置表,并关联redis 例: C('icp')*/
function c($iden)
{
    $redis = new \core\lib\redis();
    $key = 'config:' . $iden;
    $is_have = $redis->exists($key);
    if ($is_have) {
        $res = $redis->get($key); //从redis读取是空值时，也去读下数据库，为空时需弹提示
        if (empty($res) && $res != 0) {
            $db = new \app\model\config();
            $res = $db->find($iden);
            $redis->set($key, $res);

            if (empty($res) && $res != 0) {
                error($iden . '数据不存在', 404);
            }
            return $res;
        }
    } else {
        $db = new \app\model\config();
        $res = $db->find($iden);
        $redis->set($key, $res);
        if (empty($res) && $res != 0) {
            error($key . '数据不存在', 404);
        }
    }
    return $res;
}


/*强更新配置*/
function renew_c($iden)
{
    $redis = new \core\lib\redis();
    $key = 'config:' . $iden;
    $db = new \app\model\config();
    $res = $db->find($iden);
    $redis->set($key, $res);

    if (empty($res) && $res != 0) {
        error($key . '数据不存在', 404);
    }
    return $res;
}


/*读取用户不能自定义的config文件夹里文件形式保存的配置 例:cc('web_config','api') cc('public_key')*/
function cc($file, $name = '')
{
    if (empty($name)) {
        $ar = \core\lib\Config::all($file);
        return $ar;
    } else {
        $ar = \core\lib\Config::get($file, $name);
        return $ar;
    }
}


/*查插件是否开放,redis 返回boolean*/
function plugin_is_open($iden)
{
    $redis = new \core\lib\redis();
    $key = 'plugin:' . $iden;
    $is_have = $redis->exists($key);
    if ($is_have) {
        $res = $redis->get($key); //从redis读取是空值时，也去读下数据库，为空时需弹提示
        if (empty($res) && $res != 0) {
            die($res);
            $plugin_M = new \app\model\plugin();
            $res = $plugin_M->find_open($iden);
            $redis->set($key, $res);
            if (empty($res) && $res != 0) {
                error($iden . '数据不存在', 404);
            }
        }
    } else {
        $plugin_M = new \app\model\plugin();
        $res = $plugin_M->find_open($iden);
        $redis->set($key, $res);
        if (empty($res) && $res != 0) {
            error($key . '数据不存在', 404);
        }
    }
    return $res;
}


/*查插件是否开放,redis 返回boolean*/
function res_plugin_is_open($iden)
{
    $plugin_M = new \app\model\plugin();
    $res = $plugin_M->find_open($iden);
    if (empty($res) && $res != 0) {
        error($iden . '数据不存在', 404);
    }
    return $res;
}



function find_server($name = '')
{
    $date = core\lib\Request::instance()->server($name);
    return $date;
}


function set_cookie($name = '', $value = '', $expire = '', $path = '', $domain = '', $secure = '')
{
    if ($expire == '') {
        $expire = time() + 24 * 60 * 60 * 30;
    }
    if ($path == '') {
        $path = '/' . find_server("app");
    }
    $cookies = cc('web_config', 'cookies');
    foreach ($cookies as $vo) {
        setcookie($name, $value, $expire, "/", $vo, $secure);
    }
}



/**
 * 管理员日志写入
 */
function admin_log($info, $id = 0)
{
    $add['info_id'] = $id;
    $add['log_ip'] = ip();
    $add['log_info'] = $info;
    $add['log_url'] = find_server('REQUEST_URI');
    $add['username'] = $GLOBALS['admin']['username'];
    $admin_log = new \app\model\log();
    return $admin_log->save($add);
}

/**
 * 记事本日志
 */
function txt_log($value, $file = '')
{
    $dir = IMOOC . 'public/resource/log/';
    if (is_dir($dir) == false) {
        mkdir($dir, 0777);
    }
    if ($file) {
        $logpath = $dir . '/' . date('Y-m-d') . '_' . $file . '.txt';
    } else {
        $logpath = $dir . '/' . date('Y-m-d') . '.txt';
    }
    if (is_object($value)) {
        $value = object_to_array($value);
    }
    if (is_array($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    $txt = date("Y-m-d H:i:s", time()) . ' ' . ip() . "\r\n数据：" . $value . "\r\n\r\n";
    if (file_exists($logpath)) {
        $log_f = fopen($logpath, "a+");
        fputs($log_f, $txt);
        fclose($log_f);
    } else {
        file_put_contents($logpath, $txt); //创建文件
    }
}

function object_to_array($obj)
{
    $obj = (array) $obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array) object_to_array($v);
        }
    }

    return $obj;
}



function restore_array($arr)
{
    if (!is_array($arr)) {
        return $arr;
    }
    $c = 0;
    $new = array();
    while (list($key, $value) = each($arr)) {
        if (is_array($value)) {
            $new[$c] = restore_array($value);
        } else {
            $new[$c] = $value;
        }
        $c++;
    }
    return $new;
}



function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}


//参数格式是原生（raw）的内容 请求包体
function http_post($url, $data_string)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'X-AjaxPro-Method:ShowList',
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string)
        )
    );
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}




function array_group_by($arr, $key)
{
    $grouped = [];
    foreach ($arr as $value) {
        $grouped[$value[$key]][] = $value;
    }
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $key => $value) {
            $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
            $grouped[$key] = call_user_func_array('array_group_by', $parms);
        }
    }
    return $grouped;
}


/**
 * 写入模板消息 redis队列  mbxx
 * action common/sms里的方法名
 * xid 自定义ID 根据不同的方法为不同ID 
 * ar 额外参数组  ['url','tid','piclink','desc','---']
 * 调试：加入队列链接 api.szpgbb.com/common/cs/send_wx_sms_demo  执行队列：api.szpgbb.com/common/sms/index
 */
function mb_sms($action, $xid = 0, $ar = [])
{
    $redis = new \core\lib\redis();
    $params_ar['xid'] = $xid;
    $params_ar['action'] = $action;
    $params_ar = array_merge($params_ar, $ar);
    $params = json_encode($params_ar, JSON_UNESCAPED_UNICODE);
    $redis->lpush('mbxx', $params);
}

/**
 * 金额类型中文
 * 根据reward表里的iden 查  title
 * 先查redis 没有再查数据库
 */
function find_reward_redis($iden)
{
    $redis = new \core\lib\redis();
    $key = 'reward:' . $iden;
    $is_have = $redis->exists($key);
    if ($is_have) {
        $res = $redis->get($key); //从redis读取是空值时，也去读下数据库，为空时需弹提示
        if (!$res) {
            $reward_M = new \app\model\reward();
            $res = $reward_M->find_redis($iden);
            $redis->set($key, $res);
            return $res;
        }
    } else {
        $reward_M = new \app\model\reward();
        $res = $reward_M->find_redis($iden);
        $redis->set($key, $res);
    }
    return $res;
}


/**
 * 查管理员基础信息
 */
function admin_info($aid, $field = '')
{
    $admin_M = new \app\model\admin();
    if ($field) {
        $res = $admin_M->find_one($aid, $field);
    } else {
        $res = $admin_M->find($aid); //'id','username','role_id','tel','im'
    }
    return $res;
}


/**
 * 查用户信息
 * 根据user表里的id 查
 * 先查redis 没有再查数据库
 */
function user_info($uid, $field = '')
{
    $redis = new \core\lib\redis();
    $user_M = new \app\model\user();
    $rd_name = 'user:' . $uid;
    $info = $redis->hget($rd_name);
    if (!$info || !isset($info['username'])) {
        $info = $user_M->find_all($uid);
        if ($info) {
            foreach ($info as $key => $val) {
                $redis->hset($rd_name, $key, $val);
            }
        }
    }
    if ($field) {
        if (isset($info[$field])) {
            return $info[$field];
        } else {
            return '';
        }
    } else {
        return $info;
    }
}


/**
 * 强制更新用户信息
 */
function renew_user($uid)
{
    $redis = new \core\lib\redis();
    $rd_name = 'user:' . $uid;
    $user_M = new \app\model\user();
    $ar = $user_M->find_all($uid);
    unset($ar['password']);
    unset($ar['pay_password']);
    foreach ($ar as $key => $val) {
        $redis->hset($rd_name, $key, $val);
    }
    return true;
}


function renew_user_one($uid, $info = [])
{
    $redis = new \core\lib\redis();
    $rd_name = 'user:' . $uid;
    foreach ($info as $key => $val) {
        $redis->hset($rd_name, $key, $val);
    }
    return true;
}

function renew_all()
{
    $user_M = new \app\model\user();
    $ar = $user_M->list_all();
    $i = 0;
    foreach ($ar as $one) {
        renew_user($one['id']);
        $i++;
    }
    return $i;
}

/*IP拿地址http://api.mm80.cn/mobile/power/get_ip_address*/
function get_ip_address()
{

    $ip = ip();
    $sign = 'WhwtdWrap bor-b1s col-gray03';
    $con =  file_get_contents("http://ip.tool.chinaz.com/" . $ip); //202.103.11.0
    $begin = strpos($con, $sign);

    $end = $begin + 200;
    $left_con = substr($con, $begin, $end);
    $be2 = strpos($left_con, '<span class="Whwtdhalf w50-0">');
    $left_con2 = substr($left_con, $be2 + 30, 50);

    $end3 = strpos($left_con2, '</span>');
    $left = substr($left_con2, 0, $end3);

    return $left;
}


/*VUE小数点控制,根据金额类型来控制*/
function point($value = 0, $balance_type)
{
    switch ($balance_type) {
        case 'amount':
            $res = sprintf("%.2f", $value);
            break;
        case 'integral':
            $res = sprintf("%.2f", $value);
            break;
        case 'money':
            $res = sprintf("%.2f", $value);
            break;
        default:
            $res = sprintf("%.8f", $value);
            break;
    }
    return $res;
}


/*php多个数组同键名的 键值相加合并*/
function comm_sumarrs($arr)
{
    $item = array();
    foreach ($arr as $key => $value) {

        foreach ($value as $k => $v) {
            if (isset($item[$k])) {
                $item[$k] = $item[$k] + $v;
            } else {
                $item[$k] = $v;
            }
        }
    }
    arsort($item);
    return $item;
}



/*根据IP返回地址*/
function ip_address($ip)
{
    if (isset($ip) && !empty($ip)) {
        $url = "http://apis.juhe.cn/ip/ip2addr?ip=" . $ip . "&key=2a5b14d28e00e8bac09a2dac162d5090";
        $json = https_request($url);
        $ar = json_decode($json, true);
        if ($ar['resultcode'] == 200) {
            $address = $ar['result']["area"] . $ar['result']["location"];
        } else {
            $sign = 'WhwtdWrap bor-b1s col-gray03';
            $opts = array(
                'http' =>
                array(
                    'method'  => 'GET',
                    'timeout' => 1
                )
            );
            $context  = stream_context_create($opts);
            $con =  @file_get_contents("http://ip.tool.chinaz.com/" . $ip, false, $context);
            if ($con != FALSE) {
                $begin = strpos($con, $sign);
                $end = $begin + 200;
                $left_con = substr($con, $begin, $end);
                $be2 = strpos($left_con, '<span class="Whwtdhalf w50-0">');
                $left_con2 = substr($left_con, $be2 + 30, 50);
                $end3 = strpos($left_con2, '</span>');
                $address = substr($left_con2, 0, $end3);
            } else {
                $address = $ip;
            }
        }
        return $address;
    }
}






/*根据redis时间差控制 短时间反复请求*/
function flash_god($uid)
{
    $redis = new \core\lib\redis();
    $rd_name = 'user:' . $uid;
    $rd_key  = 'flashgod';
    $last_time = $redis->hget($rd_name, $rd_key);
    $time = time();
    if (intval($time) - intval($last_time) < 5) {
        error('请求太频繁', 400);
        exit();
    }
    $redis->hset($rd_name, $rd_key, $time);
}


function spr_mall($number)
{
    return sprintf("%.2f", $number);
}

function spr_coin($number)
{
    return sprintf("%.8f", $number);
}

function spr_user($number)
{
    return sprintf("%.2f", $number);
}
