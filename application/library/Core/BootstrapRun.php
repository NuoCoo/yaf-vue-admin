<?php
namespace Core;

class BootstrapRun
{
       /**
     * GET|POST|COOKIE|REQUEST|SERVER|HTML|SAFE
     */
    public static function gpc($k, $var = 'G')
    {
        switch ($var) {
            case 'G':
                $var = &$_GET;
                break;
            case 'P':
                $var = &$_POST;
                break;
            case 'C':
                $var = &$_COOKIE;
                break;
            case 'R':
                $var = isset($_GET[$k]) ? $_GET : (isset($_POST[$k]) ? $_POST : $_COOKIE);
                break;
            case 'S':
                $var = &$_SERVER;
                break;
        }
        return isset($var[$k]) ? $var[$k] : NULL;
    }

    public static function addslashes(&$var)
    {
        if (is_array($var)) {
            foreach ($var as $k => &$v) {
                self::addslashes($v);
            }
        } else {
            $var = addslashes($var);
        }
        return $var;
    }

    public static function stripslashes(&$var)
    {
        if (is_array($var)) {
            foreach ($var as $k => &$v) {
                self::stripslashes($v);
            }
        } else {
            $var = stripslashes($var);
        }
        return $var;
    }

    public static function htmlspecialchars(&$var)
    {
        if (is_array($var)) {
            foreach ($var as $k => &$v) {
                self::htmlspecialchars($v);
            }
        } else {
            $var = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $var);
        }
        return $var;
    }

    public static function urlencode($s)
    {
        $s = urlencode($s);
        return str_replace('-', '%2D', $s);
    }

    /**
     * 编码解析防止+号丢失
     */
    public static function urldecode($s)
    {
       if(preg_match('#%[0-9A-Z]{2}#isU', $s) > 0) {
            $s = urldecode($s);
        }
        return $s;
    }

    public static function json_decode($s)
    {
        return $s === FALSE ? FALSE : json_decode($s, 1);
    }

    // 替代 json_encode
    public static function json_encode($data)
    {
        if (is_array($data) || is_object($data)) {
            $islist = is_array($data) && (empty($data) || array_keys($data) === range(0, count($data) - 1));
            if ($islist) {
                $json = '[' . implode(',', array_map(array('Run', 'json_encode'), $data)) . ']';
            } else {
                $items = Array();
                foreach ($data as $key => $value) $items[] = self::json_encode("$key") . ':' . self::json_encode($value);
                $json = '{' . implode(',', $items) . '}';
            }
        } elseif (is_string($data)) {
            $string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"';
            $json = '';
            $len = strlen($string);
            for ($i = 0; $i < $len; $i++) {
                $char = $string[$i];
                $c1 = ord($char);
                if ($c1 < 128) {
                    $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1);
                    continue;
                }
                $c2 = ord($string[++$i]);
                if (($c1 & 32) === 0) {
                    $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128);
                    continue;
                }
                $c3 = ord($string[++$i]);
                if (($c1 & 16) === 0) {
                    $json .= sprintf("\\u%04x", (($c1 - 224) << 12) + (($c2 - 128) << 6) + ($c3 - 128));
                    continue;
                }
                $c4 = ord($string[++$i]);
                if (($c1 & 8) === 0) {
                    $u = (($c1 & 15) << 2) + (($c2 >> 4) & 3) - 1;
                    $w1 = (54 << 10) + ($u << 6) + (($c2 & 15) << 2) + (($c3 >> 4) & 3);
                    $w2 = (55 << 10) + (($c3 & 15) << 6) + ($c4 - 128);
                    $json .= sprintf("\\u%04x\\u%04x", $w1, $w2);
                }
            }
        } else {
            $json = strtolower(var_export($data, true));
        }

        return $json;
    }

    // 是否为命令行模式
    public static function is_cmd()
    {
        return !isset($_SERVER['REMOTE_ADDR']);
    }

    public static function ob_handle($s)
    {
        if (!empty($_SERVER['ob_stack'])) {
            $gzipon = array_pop($_SERVER['ob_stack']);
        } else {
            // throw new Exception('');
            $gzipon = 0;
        }
        $isfirst = count($_SERVER['ob_stack']) == 0;
        if ($gzipon && !ini_get('zlib.output_compression') && function_exists('gzencode') && strpos(self::gpc('HTTP_ACCEPT_ENCODING', 'S'), 'gzip') !== FALSE) {
            $s = gzencode($s, 5); // 0 - 9 级别, 9 最小','最耗费 CPU
            $isfirst && header("Content-Encoding: gzip");
            //$isfirst && header("Vary: Accept-Encoding");	// 下载的时候','IE 6 会直接输出脚本名','而不是文件名！非常诡异！估计是压缩标志混乱。
            $isfirst && header("Content-Length: " . strlen($s));
        } else {
            // PHP 强制发送的 gzip 头
            if (ini_get('zlib.output_compression')) {
                $isfirst && header("Content-Encoding: gzip");
            } else {
                $isfirst && header("Content-Encoding: none");
                $isfirst && header("Content-Length: " . strlen($s));
            }
        }
        return $s;
    }

    public static function ob_start($gzip = TRUE)
    {
        !isset($_SERVER['ob_stack']) && $_SERVER['ob_stack'] = array();
        array_push($_SERVER['ob_stack'], $gzip);
        ob_start(array('Core\BootstrapRun', 'ob_handle'));
    }


    public static function ob_end_clean()
    {
        !empty($_SERVER['ob_stack']) && count($_SERVER['ob_stack']) > 0 && ob_end_clean();
    }

    public static function ob_clean()
    {
        !empty($_SERVER['ob_stack']) && count($_SERVER['ob_stack']) > 0 && ob_clean();
    }

      /**
     * 优雅输出print_r()函数所要输出的内容
     *
     * 用于程序调试时,完美输出调试数据,功能相当于print_r().当第二参数为true时(默认为:false),功能相当于var_dump()。
     * 注:本方法一般用于程序调试
     * @access public
     * @param array $data 所要输出的数据
     * @param boolean $option 选项:true或 false
     * @return array            所要输出的数组内容
     */
    public static function dump($data, $option = false)
    {

        //当输出print_r()内容时
        if (!$option) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        } else {
            ob_start();
            var_dump($data);
            $output = ob_get_clean();

            $output = str_replace('"', '', $output);
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

            echo '<pre>', $output, '</pre>';
        }

        exit;
    }
}