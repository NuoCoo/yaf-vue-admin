<?php
namespace Master\Utils;

class Misc
{



        /**
     * 递归
     * @param array $array 数据
     * @param string $index key
     * @param string $pid 父级key
     *
     * @return   array
     */
    public static function recursion($array, $index = 'id', $pid = 'parent_id'){
        //第一步 构造数据
        $items = [];
        if(empty($array)){
            return [];
        }
        foreach($array as $value){
            $items[$value[$index]] = $value;
        }

        //第二部 遍历数据 生成树状结构
        $tree = array();
        foreach($items as $key => $item){
            if(isset($items[$item[$pid]])){
                $items[$item[$pid]]['children'][] = &$items[$key];
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }


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