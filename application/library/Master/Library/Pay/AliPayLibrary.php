<?php
namespace Master\Library\Pay;
/**
 * 支付宝支付
 * Created by PhpStorm.
 * User: JiYuan
 * Date: 2017/3/28
 * Time: 17:25
 */
class AliPayLibrary
{
    /**
     * 获取支付宝key
     * @param string $pay_code 支付方式 alipay_mobile|alipay_wap|alipay_qr
     * @param string $key_type
     * @return string|bool 返回读取文件得到的字符串, 或在读取失败时返回false
     */
    public function getKey($pay_code, $key_type){
        // key文件路径
        $key_path = APP_PATH . '/data/key/' . $pay_code . '/' . $key_type . '.pem';

        // 文件不存在
        if (!is_file($key_path)) {
            return false;
        }
        // 读取文件, 并返回内容
        return file_get_contents($key_path);
    }


    /**
     * 除去数组中的空值和签名参数
     * @param array $params 签名参数组
     * @return array 去掉空值与签名参数后的新签名参数组
     */
    public function paramFilter($params) {
        $params_filter = [];
        foreach ($params as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val === "") {
                continue;
            } else {
                $params_filter[$key] = $params[$key];
            }
        }
        return $params_filter;
    }

    /**
     * 对数组排序
     * @param array $params 排序前的数组
     * @return array 排序后的数组
     */
    public function paramsArraySort($params)
    {
        ksort($params);
        reset($params);
        return $params;
    }

    /**
     * 生成支付宝签名字符串
     * @param array $params 签名参数数组
     * @param bool $quote 参数是否需要引号包裹
     * @return string
     */
    public function createLinkString($params, $quote = true)
    {
        // 把参数数组, 按照“参数=参数值”的模式用“&”字符拼接成字符串。
        $string = "";

        if ($quote) {
            foreach ($params as $key => $val) {
                if (!$string) {
                    $string .= $key . '="' . $val . '"';
                } else {
                    $string .= '&' . $key . '="' . $val . '"';
                }
            }
        } else {
            foreach ($params as $key => $val) {
                if (!$string) {
                    $string .= $key . '=' . $val;
                } else {
                    $string .= '&' . $key . '=' . $val;
                }
            }
        }


        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }

        return $string;
    }

    /**
     * 生成支付宝签名
     * @param string $pay_code 支付方式
     * @param string $data 签名字符串
     * @param string $sign_type 签名生成方式
     * @return string
     */
    public function createSign($pay_code, $data, $sign_type = 'RSA')
    {
        // 支付宝私有秘钥
        $rsa_private_key = $this->getKey($pay_code, 'rsa_private_key');
        if (false === $rsa_private_key) {
            return false;
        }

        // 以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $rsa_private_key = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $rsa_private_key);
        $rsa_private_key = str_replace("-----END RSA PRIVATE KEY-----", "", $rsa_private_key);
        $rsa_private_key = str_replace("\n", "", $rsa_private_key);
        $rsa_private_key = "-----BEGIN RSA PRIVATE KEY-----" . PHP_EOL . wordwrap($rsa_private_key, 64, "\n", true) . PHP_EOL . "-----END RSA PRIVATE KEY-----";

        $res = openssl_get_privatekey($rsa_private_key);
        if (!$res) {
            // 私钥格式不正确
            return false;
        }

        // 生成签名
        switch ($sign_type) {
            case "RSA":
                openssl_sign($data, $sign, $res);
                openssl_free_key($res);
                break;
            default :
                // 签名方式错误
                return false;
                break;
        }

        // base64编码
        return base64_encode($sign);
    }

    /**
     * 生成支付宝签名
     * @param array $payments 支付方式
     * @param string $data 签名字符串
     * @param string $sign_type 签名生成方式
     * @return string
     */
    public function createGroupSign($payments, $data)
    {
        // 支付宝私有秘钥
        $rsa_private_key = $this->getCertContent($payments['group_id'],'rsa_private_key');
        if (false === $rsa_private_key) {
            return false;
        }

        // 以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $rsa_private_key = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $rsa_private_key);
        $rsa_private_key = str_replace("-----END RSA PRIVATE KEY-----", "", $rsa_private_key);
        $rsa_private_key = str_replace("\n", "", $rsa_private_key);
        $rsa_private_key = "-----BEGIN RSA PRIVATE KEY-----" . PHP_EOL . wordwrap($rsa_private_key, 64, "\n", true) . PHP_EOL . "-----END RSA PRIVATE KEY-----";

        $res = openssl_get_privatekey($rsa_private_key);
        if (!$res) {
            // 私钥格式不正确
            return false;
        }

        if ("RSA2" == $payments['sign_type']) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        openssl_free_key($res);
        // base64编码
        return base64_encode($sign);
    }

    /**
     * 获取支付宝key
     * @param string $group_id 支付方式 alipay_mobile|alipay_wap|alipay_qr
     * @param string $key_type
     * @return string|bool 返回读取文件得到的字符串, 或在读取失败时返回false
     */
    public function getCertContent($group_id, $key_type)
    {
        // key文件路径
        $key_path = '/data/wwwroot/cert/alipay/group_'.$group_id.'/' . $key_type . '.pem';
        // 文件不存在
        if (!is_file($key_path)) {
            return false;
        }

        // 读取文件, 并返回内容
        return file_get_contents($key_path);
    }

    /**
     * 判断是否是支付宝发来的异步通知
     * @param array $payment_config 支付配置信息
     * @param string $notify_id 异步通知ID
     * @return boolean
     */
    public function responseVerify($payment_config, $notify_id)
    {
        // 验证地址
        if(misc::is_ssl()) {
            $veryfy_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
        } else {
            $veryfy_url = 'http://notify.alipay.com/trade/notify_query.do?';
        }
        $veryfy_url = $veryfy_url . "partner=" . $payment_config['partner'] . "&notify_id=" . $notify_id;

        // 证书地址
        $cacert_url = APP_PATH . '/data/key/' . $payment_config['pay_code'] . '/cacert.pem';

        // 发起验证
        $curl = curl_init($veryfy_url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);// SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);// 严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);// 证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText == 'true';
    }

    /**
     * 签名验证
     * 注意:验证签名时, 与生成支付字符串时的签名规则不同:
     * 1. 验证签名时参数字段需要排序, 生成支付字符串签名时参数无需排序
     * 2. 验签时数组转字符串value无需加"", 生成支付字符串签名时需要.
     * @param array $payment_config 支付方式配置信息
     * @param array $request_params 请求参数
     * @return bool
     */
    public function signVerify($payment_config, $request_params)
    {
        // 去掉请求参数中的空值 和 不参与签名的参数
        $params = $this->paramFilter($request_params);

        // 数组排序
        $params = $this->paramsArraySort($params);

        // 待签名字符串
        $string = $this->createLinkString($params, false);

        // 支付宝公开key
        $alipay_public_key = $this->getKey($payment_config['pay_code'], 'alipay_public_key');

        return $this->rsaVerify($string, trim($alipay_public_key), $request_params['sign']);
    }

    /**
     * RSA验签
     * @param string $data 待签名数据
     * @param string $alipay_public_key 支付宝的公钥字符串
     * @param string $sign 要校对的的签名结果
     * @return boolean 验证结果
     */
    public function rsaVerify($data, $alipay_public_key, $sign)
    {
        // 以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $alipay_public_key = str_replace("-----BEGIN PUBLIC KEY-----", "", $alipay_public_key);
        $alipay_public_key = str_replace("-----END PUBLIC KEY-----", "", $alipay_public_key);
        $alipay_public_key = str_replace("\n", "", $alipay_public_key);
        $alipay_public_key = '-----BEGIN PUBLIC KEY-----' . PHP_EOL . wordwrap($alipay_public_key, 64, "\n", true) . PHP_EOL . '-----END PUBLIC KEY-----';


        $res = openssl_get_publickey($alipay_public_key);
        if($res) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        } else {
            $result = false;
        }
        openssl_free_key($res);
        return $result;
    }

    /**
     * 发送一个POST请求
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @param string $input_charset 字符集
     * @return mixed
     */
    public function getHttpResponsePOST($url, $params, $input_charset = '')
    {
        if (trim($input_charset) != '') {
            $url = $url . "_input_charset=" . $input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);//严格认证
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);// post传输数据
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        return $responseText;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param array $para_temp 请求参数数组
     * @return string 提交表单HTML文本
     */
    public function buildRequestForm($para_temp) {
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=UTF-8' method='POST'>";
        while (list ($key, $val) = each ($para_temp)) {
            if (false === $this->checkEmpty($val)) {
                $val = str_replace("'","&apos;",$val);
                $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
            }
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     */
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }


}
