<?php
namespace Core;
/**
 * 日志类
 * @version 1.0
 */
class Log
{
    // 日志信息
    protected static $log = [];
    // 日志类型
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug'];

    protected static $save_config = [
        'file_size'   => 2097152,
        'path'        => LOG_PATH,
        'apart_level' => [],
    ];


    /**
     * 获取日志信息
     * @param string $type 信息类型
     * @return array
     */
    public static function getLog($type = '')
    {
        return $type ? self::$log[$type] : self::$log;
    }

    /**
     * 记录调试信息
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     * @param bool   $force 是否强制写入
     * @return void
     */
    public static function record($msg, $type = 'log', $force = true)
    {
        self::$log[$type][] = $msg;
        if ($force === true) {
            self::save(false);
        }
    }

    /**
     * 清空日志信息
     * @return void
     */
    public static function clear()
    {
        self::$log = [];
    }

    /**
     * 保存调试信息
     * @param bool  $depr 是否写入分割线
     * @return bool
     */
    public static function save($depr = true)
    {
        if (!empty(self::$log)) {

            // 获取全部日志
            $log = self::$log;
            if (!DEBUG && isset($log['debug'])) {
                unset($log['debug']);
            }

            $result = self::_save($log, $depr);
            if ($result) {
                self::$log = [];
            }

            return $result;
        }
        return true;
    }

    /**
     * 实时写入日志信息 并支持行为
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     * @param bool   $force 是否强制写入
     * @return bool
     */
    public static function write($msg, $type = 'log')
    {
        // 封装日志信息
        $log[$type][] = $msg;
        // 写入日志
        return self::_save($log, false);
    }

    /**
     * 日志写入接口
     * @access private
     * @param array $log 日志信息
     * @param bool  $depr 是否写入分割线
     * @return bool
     */
    private static function _save(array $log = [], $depr = true)
    {
        $now         = date('Y-m-d H:i:s');
        $destination =self::$save_config['path'] . DS . date('Ym') . DS . date('Ymd') . '.log';

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor(self::$save_config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
        }

        $depr = $depr ? "---------------------------------------------------------------\r\n" : '';
        $info = '';
        if (DEBUG) {
            // 获取基本信息
            if (isset($_SERVER['HTTP_HOST'])) {
                $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            } else {
                $current_uri = "cmd:" . implode(' ', $_SERVER['argv']);
            }

            $runtime    = round(microtime(true) - $_SERVER['start_time'], 10);
            $reqs       = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
            $time_str   = ' [运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
            $memory_use = number_format((memory_get_usage() - $_SERVER['start_mem']) / 1024, 2);
            $memory_str = ' [内存消耗：' . $memory_use . 'kb]';

            $info   = '[ log ] ' . $current_uri . $time_str . $memory_str . "\r\n";
            $server = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
            $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
            $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
            $uri    = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        }
        foreach ($log as $type => $val) {
            $level = '';
            foreach ($val as $msg) {
                if (!is_string($msg)) {
                    $msg = var_export($msg, true);
                }
                $level .= '[ ' . $type . ' ] ' . $msg . "\r\n";
            }
            if (in_array($type, self::$save_config['apart_level'])) {
                // 独立记录的日志级别
                $filename = $path . DS . date('d') . '_' . $type . '.log';
                error_log("[{$now}] {$level}\r\n{$depr}", 3, $filename);
            } else {
                $info .= $level;
            }
        }
        if (DEBUG) {
            $info = "{$server} {$remote} {$method} {$uri}\r\n" . $info;
        }
        return error_log("[{$now}] {$info}\r\n{$depr}", 3, $destination);
    }


    /**
     * 静态调用
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            array_push($args, $method);
            return call_user_func_array('Log::record', $args);
        }
    }

}
