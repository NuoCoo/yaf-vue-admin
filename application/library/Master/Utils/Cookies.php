<?php
namespace Master\Utils;

class Cookies
{
    public static $ali_pay = 'WEB2_ALIPAY_USER';

    public static $weChat_pay = 'WEB2_WECHAT_USER';

    public static function setAliPayUsers($params){
        self::setCookies(self::$ali_pay, json_encode($params), 99999999999, '/');
        return true;
    }

    public static function getAliPayUsers($index = 'id'){

        if (array_key_exists(self::$ali_pay, $_COOKIE) && isset($_COOKIE[self::$ali_pay]) && !empty($_COOKIE[self::$ali_pay])) {
            $users = json_decode($_COOKIE[self::$ali_pay], true);
            if(empty($users)){
                return 0;
            }
            if(!isset($users[$index])){
                return 0;
            }
            return $users[$index];
        }
        return 0;
    }


    public static function setWeChatUsers($params){
        self::setCookies(self::$weChat_pay, json_encode($params), 99999999999, '/');
        return true;
    }

    public static function getWeChatUsers($index = ''){

        if (array_key_exists(self::$weChat_pay, $_COOKIE) && isset($_COOKIE[self::$weChat_pay]) && !empty($_COOKIE[self::$weChat_pay])) {
            $users = json_decode($_COOKIE[self::$weChat_pay], true);
            if(empty($users)){
                return 0;
            }
            if($index && isset($users[$index])){
                return $users[$index];
            }
            return $users;
        }
        return 0;
    }

    public static function setCookies($key, $value, $time = 0, $path = '', $domain = '', $httponly = TRUE)
    {
        // 计算时差','服务器时间和客户端时间不一致的时候','最好由客户端写入。
        $_COOKIE[$key] = $value;
        if ($value != NULL) {
            if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
                setcookie($key, $value, $time, $path, $domain, FALSE, $httponly);
            } else {
                setcookie($key, $value, $time, $path, $domain, FALSE);
            }
        } else {
            if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
                setcookie($key, '', $time, $path, $domain, FALSE, $httponly);
            } else {
                setcookie($key, '', $time, $path, $domain, FALSE);
            }
        }
    }
}