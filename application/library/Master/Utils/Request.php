<?php
namespace Master\Utils;
use Master\Utils\Core;

class Request
{

    public static function get($index = '', $mold = '', $default = ''){
        if(empty($index)){
            return $_GET;
        }
        if(!isset($_GET[$index])){
            return $default;
        }
        $value = $_GET[$index];
        switch ($mold){
            case 'int':
                if(is_numeric($value)){
                    $value = (int)$value;
                } else {
                    $value = $default;
                }
                return $value ? $value : $default;
                break;
            case 'string':
                $value = Core::removeXss($value);
                return $value ? $value : $default;
                break;
            case 'bool':
                if($value == 'on' || $value == 'true'){
                    $value  = 1;
                } else {
                    $value = 0;
                }
                return $value;
                break;
            default:
                return $value;
                break;
        }
    }

    public static function post($index = '', $mold = '', $default = ''){
        self::body();
        if(empty($index)){
            return $_POST;
        }
        if(!isset($_POST[$index])){
            return $default;
        }
        $value = $_POST[$index];
        switch ($mold){
            case 'int':
                if(is_numeric($value)){
                    $value = (int)$value;
                } else {
                    $value = $default;
                }
                return $value ? $value : $default;
                break;
            case 'string':
                $value = Core::removeXss($value);
                return $value ? $value : $default;
                break;
            case 'bool':
                if($value == 'on' || $value == 'true'){
                    $value  = 1;
                } else {
                    $value = 0;
                }
                return $value;
                break;
            default:
                return $value;
                break;
        }
    }

    public static function body(){

        $body_contents = file_get_contents('php://input');
        if (!empty($body_contents)) {
            if (false !== strpos($body_contents, 'xml')) {
                $body_contents = json_decode(json_encode(simplexml_load_string($body_contents, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            } else {
                $body_contents = json_decode($body_contents, true);
            }
            if (!empty($body_contents)) {
                $_POST = array_merge($_POST, $body_contents);
            }
        }
    }

}