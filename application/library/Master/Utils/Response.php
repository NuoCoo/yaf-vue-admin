<?php
namespace Master\Utils;

class Response
{
    /**
     * 以json返回数据
     * @param string $msg 提示
     * @param int $status 状态
     * @param array $result 返回数据
     * @param array $params 附加参数
     * @return void
     */
    public static function json($status = 101, $msg = '', $result = [], $params = []){
        header('Content-type:application/json;charset=utf-8');
        $response = ['code' => intval($status), 'msg' => $msg, 'result'=>$result];
        //判断是否有其他返回参数
        if (!empty($params)) {
            foreach ($params as $k=>$v) {
                $response[$k] = $v;
            }
        }
        exit(json_encode($response, JSON_ERROR_RECURSION));
    }


}