<?php
namespace Master\Utils;

class Misc
{


    /**
     * 生成流水码
     * @return string  流水码
     */
    public static function createOrderSn()
    {
        $micro = explode(" ", microtime())[0];
        //从.号往后截取4位以解决类似0.0023 被转为23的情形
        $micro = substr($micro, strpos($micro, '.')+1, 6);
        $sn = date('YmdHis') . $micro.sprintf("%'05d", mt_rand(00000, 99999));
        return $sn;
    }



    public static function setArrayKeys($arr, $keys = 'id'){
        $params = [];
        if(empty($arr)){
            return false;
        }
        foreach ($arr as $value){
            $params[$value[$keys]] = $value;
        }
        return $params;
    }


}