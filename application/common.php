<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
use think\Request;

if (!function_exists("imf_user_login")) {
    function imf_admin_login()
    {
        if (!session('userid')) {
            $request = new Request();
            header('Location:/auth/index/login?redirect=' . $request->domain() . '/admin');
        }
    }
};

function getDomain()
{
    $request = new Request();
    return $request->domain();
}

function imf_get_user_menu($rid)
{
    //获取老的用户权限
    $UserType = new app\admin\model\UserType();
    $oldRightsStr = $UserType->where([
        'id' => $rid
    ])->field('menu_dict')->select()->toArray();
    if (isset($oldRightsStr[0]['menu_dict'])) {
        $oldRightsArr = explode('|', $oldRightsStr[0]['menu_dict']);
    } else {
        $oldRightsArr = [];
    }
    $allMenu = app\admin\model\MenuModel::AllList();
    $newMenu = [];
    foreach ($allMenu as $k => $v) {
        $v['checked'] = '';
        if (in_array($v['id'], $oldRightsArr)) {
            $v['checked'] = 'checked';
        }
        $v['submenu'] = '';
        $allMenu[$k] = $v;

    }
    foreach ($allMenu as $k => $v) {
        if ($v['parent_id'] == 0) {
            $v['submenu'] = [];
            foreach ($allMenu as $k2 => $v2) {
                if ($v2['parent_id'] == $v['id']) {
                    array_push($v['submenu'], $v2);
                }
            }
            $newMenu[$k] = $v;
        }
    }
    return $newMenu;
}

function imf_rand_str($length = 8, $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): String
{
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $str[mt_rand(0, strlen($str) - 1)];
    }
    return $string;
}

//密码加密
function passwordHash($password): string
{
    return password_hash($password, PASSWORD_BCRYPT, [
        'cost' => 10
    ]);
}

//密码解密
function passwordVerify($password, $hashPassword): bool
{
    if (password_verify($password, $hashPassword)) {
        return true;
    }
    return false;
}


/**
 * @param $URL 请求链接
 * @param null $data 数据 array() string
 * @param string $type 请求类型 POST GET PUT DELETE
 * @param string $headers 头部信息
 * @return mixed
 */
function httpRequest($URL, $data = null, $type = 'GET', $headers = [])
{
    $ch = curl_init();
    //判断ssl连接方式
    if (stripos($URL, 'https://') !== false) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    }
    $connttime = 300; //连接等待时间500毫秒
    $timeout = 15000;//超时时间15秒
    $querystring = "";
    if (is_array($data)) {
        // Change data in to postable data
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $val2) {
                    $querystring .= urlencode($key) . '=' . urlencode($val2) . '&';
                }
            } else {
                $querystring .= urlencode($key) . '=' . urlencode($val) . '&';
            }
        }
        $querystring = substr($querystring, 0, -1); // Eliminate unnecessary &
    } else {
        $querystring = $data;
    }
    if ($type == 'GET') {
        $URL = $URL . '?' . $querystring;
    }
    //exit($URL);
    curl_setopt($ch, CURLOPT_URL, $URL); //发贴地址
    //设置HEADER头部信息
    if (!$headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//反馈信息
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //http 1.1版本

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connttime);//连接等待时间
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);//超时时间

    switch ($type) {
        case "GET" :
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
            break;
        case "PUT" :
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
            break;
    }
    $file_contents = curl_exec($ch);//获得返回值
    $status = curl_getinfo($ch);
    curl_close($ch);
    return $file_contents;
}

/*
* 检测链接是否是SSL连接
* @return bool
*/
function is_SSL()
{
    if (!isset($_SERVER['HTTPS']))
        return FALSE;
    if ($_SERVER['HTTPS'] === 1) {  //Apache
        return TRUE;
    } elseif ($_SERVER['HTTPS'] === 'on') { //IIS
        return TRUE;
    } elseif ($_SERVER['SERVER_PORT'] == 443) { //其他
        return TRUE;
    }
    return FALSE;
}

function ImfHttpRequest($url = null, $method = 'get', $headers = [], $params = [], $timeout = 60)
{
    if (is_array($params)) {
        $requestString = http_build_query($params);
    }
    if (empty($headers)) {
        $headers = ['Content-type: text/json'];
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 180);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    switch (strtoupper($method)) {
        case "GET" :
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
            break;
        case "PUT" :
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
            break;
    }
    $response = curl_exec($ch);
    $responseInfo = curl_getinfo($ch);
    //var_dump($responseInfo);
    if (200 != $responseInfo['http_code']) {
        return [
            'error' => curl_error($ch),
            'errno' => curl_errno($ch),
            'url' => $url,
        ];
    } else {
        return $response;
    }
    curl_close($ch);
}

/**
 * 生成数字号码(no-repeat)
 * @param int $num
 * return string
 */
function numCodeNoRe($num = 4)
{
    $string = "0123456789";
    $return_str = substr(str_shuffle($string), 0, $num);
    return $return_str;
}

/**
 * 生成数字号码(repeat)
 * @param type $num
 * @return string
 */
function numCode($num = 4)
{
    $string = "0123456789";
    $return_str = '';
    $i = 0;
    do {
        $i++;
        $return_str .= $string[mt_rand(0, 9)];
    } while ($i < $num);

    return $return_str;
}


function getProvinceCity($ip)
{
    $api = 'http://ip.ws.126.net/ipquery';
    $params = array('ip' => $ip);
    $content = curl_get($api, $params);
    $content = iconv("gbk", "utf8", $content);
    if ($content) {
        $pattern = '/city:"([\s\S]*)", province:"([\s\S]*)"/';
        preg_match($pattern, $content, $matches);
        $result['city'] = $matches[1];
        $result['province'] = $matches[2];
        return $result;
    }
    return $content;
}

//字符串加密
function encrypt($data, $key)
{
    $key = md5($key);
    $x = 0;
    $char = "";
    $str = "";
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}

//字符串解密
function decrypt($data, $key)
{
    $key = md5($key);
    $x = 0;
    $char = "";
    $str = "";
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

/*
6217 9873 00000 845821

533525197103081433

18721662013

686844

150 120 15274
*/
/**
 * 生成唯一字符串组合
 * @param int $num
 * @return string
 */
function uniqueString($num = 18)
{
    $string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $return_str = '';
    $i = 0;
    do {
        $i++;
        $return_str .= $string[mt_rand(0, 61)];
    } while ($i < $num);

    return $return_str;
}

/**
 * 生成唯一数组合
 * @param int $num
 * @return string
 */
function uniqueNumber($num = 18)
{
    $string = "123456789";
    $return_str = '';
    $i = 0;
    do {
        $i++;
        $return_str .= $string[mt_rand(0, 8)];
    } while ($i < $num);

    return $return_str;
}

/**
 * 生成数字号码(repeat)
 * @param type $num
 * @return string
 */
function numCodeRe($num = 4)
{
    $string = "0123456789";
    $return_str = '';
    $i = 0;
    do {
        $i++;
        $return_str .= $string[mt_rand(0, 9)];
    } while ($i < $num);

    return $return_str;
}

function genUuid(): string
{
    if (function_exists('atom_next_id')) {
        return atom_next_id();
    }
    return uniqueNumber(6) . uniqueNumber(6) . uniqueNumber(6);
}

function parseUid($uid): array
{
    if (function_exists('atom_explain')) {
        return atom_explain($uid);
    }
    return false;
}


//获取密码强度0->低、1->较低、2->中、3->较高、4->高
function passwordStrength($password)
{
    $score = 0;
    if (preg_match("/[0-9]+/", $password)) {
        $score++;
    }
    if (preg_match("/[0-9]{3,}/", $password)) {
        $score++;
    }
    if (preg_match("/[a-z]+/", $password)) {
        $score++;
    }
    if (preg_match("/[a-z]{3,}/", $password)) {
        $score++;
    }
    if (preg_match("/[A-Z]+/", $password)) {
        $score++;
    }
    if (preg_match("/[A-Z]{3,}/", $password)) {
        $score++;
    }
    if (preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/", $password)) {
        $score += 2;
    }
    if (preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]{3,}/", $password)) {
        $score++;
    }
    if (strlen($password) >= 10) {
        $score++;
    }
    //返回0,1,2,3,4
    return ceil($score / 2);
}


function object_to_array($obj)
{
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }

    return $obj;
}

function object2array($object)
{
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    } else {
        $array = $object;
    }
    return $array;
}

//获取当前用户ip
function getIp()
{
    $unknown = 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    /**
     * 处理多层代理的情况
     * 或者使用正则方式：$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
     */
    if (false !== strpos($ip, ',')) $ip = reset(explode(',', $ip));
    $ipinf = getIpInfo($ip);
    if ($ipinf['city'] == '内网IP') {
        $ipinf['city'] = '上海市';
    }
    return $ipinf;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0)
{
    $type = $type ? 1 : 0;
    static $ip = null;
    if (null !== $ip) {
        return $ip[$type];
    }

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) {
            unset($arr[$pos]);
        }

        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function getIpInfo($ip)
{
    $url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
//        $url='http://ip.taobao.com/service/getIpInfo.php?ip=169.235.24.133';
    $result = file_get_contents($url);
    $result = json_decode($result, true);
    if ($result['code'] !== 0 || !is_array($result['data'])) return false;
    return $result['data'];
}


/**
 * @param $URL 请求链接
 * @param null $data 数据 array() string
 * @param string $type 请求类型 POST GET PUT DELETE
 * @param string $headers 头部信息
 * @return mixed
 */
function curl_get($URL, $data = null, $type = 'GET', $headers = [])
{
    $ch = curl_init();
    //判断ssl连接方式
    if (stripos($URL, 'https://') !== false) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    }
    $connttime = 300; //连接等待时间500毫秒
    $timeout = 15000;//超时时间15秒
    $querystring = "";
    if (is_array($data)) {
        // Change data in to postable data
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $val2) {
                    $querystring .= urlencode($key) . '=' . urlencode($val2) . '&';
                }
            } else {
                $querystring .= urlencode($key) . '=' . urlencode($val) . '&';
            }
        }
        $querystring = substr($querystring, 0, -1); // Eliminate unnecessary &
    } else {
        $querystring = $data;
    }
    if ($type == 'GET') {
        $URL = $URL . '?' . $querystring;
    }
    //exit($URL);
    curl_setopt($ch, CURLOPT_URL, $URL); //发贴地址
    //设置HEADER头部信息
    if (!$headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//反馈信息
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //http 1.1版本

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connttime);//连接等待时间
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);//超时时间

    switch ($type) {
        case "GET" :
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
            break;
        case "PUT" :
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
            break;
    }
    $file_contents = curl_exec($ch);//获得返回值
    $status = curl_getinfo($ch);
    curl_close($ch);
    return $file_contents;
}

//创建TOKEN
function createToken()
{
    $code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
    session('TOKEN', authcode($code));
}

//判断TOKEN
function checkToken($token)
{
    if ($token == session('TOKEN')) {
        session('TOKEN', NULL);
        return TRUE;
    } else {
        return FALSE;
    }
}

/* 加密TOKEN */
function authcode($str)
{
    $key = "YOURKEY";
    $str = substr(md5($str), 8, 10);
    return md5($key . $str);
}

function RedisInstance()
{
    //think\Config::load(APP_PATH.'/../config/redis.php');
    $client = new Predis\Client([
        'host' => config('redis.host'),
        'port' => config('redis.port'),
    ]);
    $client->auth(config('redis.password'));
    return $client;

}

function imf_substring($str, $lenth, $start = 0)
{
    $len = strlen($str);
    $r = array();
    $n = 0;
    $m = 0;

    for ($i = 0; $i < $len; $i++) {
        $x = substr($str, $i, 1);
        $a = base_convert(ord($x), 10, 2);
        $a = substr('00000000 ' . $a, -8);

        if ($n < $start) {
            if (substr($a, 0, 1) == 0) {
            } else if (substr($a, 0, 3) == 110) {
                $i += 1;
            } else if (substr($a, 0, 4) == 1110) {
                $i += 2;
            }
            $n++;
        } else {
            if (substr($a, 0, 1) == 0) {
                $r[] = substr($str, $i, 1);
            } else if (substr($a, 0, 3) == 110) {
                $r[] = substr($str, $i, 2);
                $i += 1;
            } else if (substr($a, 0, 4) == 1110) {
                $r[] = substr($str, $i, 3);
                $i += 2;
            } else {
                $r[] = ' ';
            }
            if (++$m >= $lenth) {
                break;
            }
        }
    }
    return join('', $r);
}

function imf_parse_sql($sql = '', $limit = 0, $prefix = [])
{
    // 被替换的前缀
    $from = '';
    // 要替换的前缀
    $to = '';

    // 替换表前缀
    if (!empty($prefix)) {
        $to = current($prefix);
        $from = current(array_flip($prefix));
    }

    if ($sql != '') {
        // 纯sql内容
        $pure_sql = [];

        // 多行注释标记
        $comment = false;

        // 按行分割，兼容多个平台
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        $sql = explode("\n", trim($sql));

        // 循环处理每一行
        foreach ($sql as $key => $line) {
            // 跳过空行
            if ($line == '') {
                continue;
            }

            // 跳过以#或者--开头的单行注释
            if (preg_match("/^(#|--)/", $line)) {
                continue;
            }

            // 跳过以/**/包裹起来的单行注释
            if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                continue;
            }

            // 多行注释开始
            if (substr($line, 0, 2) == '/*') {
                $comment = true;
                continue;
            }

            // 多行注释结束
            if (substr($line, -2) == '*/') {
                $comment = false;
                continue;
            }

            // 多行注释没有结束，继续跳过
            if ($comment) {
                continue;
            }

            // 替换表前缀
            if ($from != '') {
                $line = str_replace('`' . $from, '`' . $to, $line);
            }
            if ($line == 'BEGIN;' || $line == 'COMMIT;') {
                continue;
            }
            // sql语句
            array_push($pure_sql, $line);
        }

        // 只返回一条语句
        if ($limit == 1) {
            return implode($pure_sql, "");
        }

        // 以数组形式返回sql语句
        $pure_sql = implode($pure_sql, "\n");
        $pure_sql = explode(";\n", $pure_sql);
        return $pure_sql;
    } else {
        return $limit == 1 ? '' : [];
    }
}

function BBSauthcode($string, $operation = 'DECODE', $key = '0e57e5e2d35e02d3f3d7f35deef67ef3wKrnfhkZjvgansbjTS', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key != '' ? $key : '');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }

}

function BBSdsetcookie($var, $value = '', $life = 0, $prefix = "thinkimf_", $httponly = false, $domain = '')
{
    $var = $prefix . $var;
    $_COOKIE[$var] = $value;
    if ($value == '' || $life < 0) {
        $value = '';
        $life = -1;
    }

    if (defined('IN_MOBILE')) {
        $httponly = false;
    }

    $life = $life > 0 ? time() + $life : ($life < 0 ? time() - 31536000 : 0);
    $path = $httponly && PHP_VERSION < '5.2.0' ? "/" . '; HttpOnly' : "/";

    $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
    if ($domain) {
        $domainReal = $domain;
    } else {
        $domainReal = ".thinkimf.com";
    }
    if (PHP_VERSION < '5.2.0') {
        setcookie($var, $value, $life, $path, $domainReal, $secure);
    } else {
        setcookie($var, $value, $life, $path, $domainReal, $secure);
        //setcookie($var, $value, $life, $path, $domainReal, $secure, $httponly);
    }
}

function BBSgetcookie($key)
{
    global $_G;
    return isset($_G['cookie'][$key]) ? $_G['cookie'][$key] : '';
}

function random_str($length = 16)
{
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}

function isMobile()
{
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }
    if (isset ($_SERVER['HTTP_VIA']))
    {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        }
    }
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    return false;
}


