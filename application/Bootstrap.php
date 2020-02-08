<?php

use Core\Log;
use Core\BootstrapRun as CoreInit;

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:\Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 * 注意:方法在Bootstrap类中的定义出现顺序, 决定了它们的被调用顺序.
 * User: sunfx
 * Date: 13-8-26
 * Time: 下午11:42.
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public $superglobal = array(
        'GLOBALS' => 1,
        '_GET' => 1,
        '_POST' => 1,
        '_REQUEST' => 1,
        '_COOKIE' => 1,
        '_SERVER' => 1,
        '_ENV' => 1,
        '_FILES' => 1,
    );

    public function _initSet()
    {
        // 最低版本需求判断
        PHP_VERSION < '5.4' && exit('Required PHP version 5.4.* or later.');

        //配置文件
        Yaf_Registry::set('config', Yaf_Application::app()->getConfig());
        
        define('PATH_APP', Yaf_Registry::get('config')->application->directory);

        //是否开启调试模式
        define('DEBUG', Yaf_Registry::get('config')->get('application')->showErrors);

        define('VIEW_PATH', Yaf_Registry::get('config')->application->template. "/template/views");

        define('UPLOAD_PATH', APP_PATH.'/public/uploads/'); //数据目录

        define('DATA_PATH', APP_PATH.'/data'); //数据目录

        define('LOG_PATH', DATA_PATH.'/log'); //日志目录

        define('SYS_TIME', $_SERVER['time']);

        define('DS', DIRECTORY_SEPARATOR);

        //----------------------------------> 全局设置:

        // 错误报告
        if (DEBUG) {
            // E_ALL | E_STRICT
            error_reporting(E_ALL);
            //error_reporting(E_ALL & ~(E_NOTICE | E_STRICT));
            ini_set('display_errors', 'ON');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'ON');
            // ini_set('display_errors', 'Off');
        }

        ini_set('memory_limit', '1024m');
        
        //设置市区
        date_default_timezone_set('Asia/Shanghai');

        //注销所有的超级变量
        foreach ($GLOBALS as $key => $value) {
            if (!isset($this->superglobal[$key])) {
                $GLOBALS[$key] = null;
                unset($GLOBALS[$key]);
            }
        }
        //禁止globals变量
        if (isset($_GET['GLOBALS']) || isset($_POST['GLOBALS']) || isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            exit();
        }
        // GPC 安全过滤，关闭，数据的正确性可能会受到影响。
        if (!get_magic_quotes_gpc()) {
            $_GET != false && CoreInit::stripslashes($_GET);
            $_POST != false && CoreInit::stripslashes($_POST);
            $_COOKIE != false && CoreInit::stripslashes($_COOKIE);
            $_REQUEST != false && CoreInit::stripslashes($_REQUEST);
        }
        //开启xss过滤
        //$_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_SERVER['REQUEST_URI']) && CoreInit::_xss_check();
    }

    //基础配置文件
    public function _initBase(Yaf_Dispatcher $dispatcher)
    {
        // 如果非命令行，则输出 header 头
        if (!CoreInit::is_cmd()) {
            header('Expires: 0');
            header('Cache-Control: private, post-check=0, pre-check=0, max-age=0');
            header('Pragma: no-cache');
            header('Content-Type: text/html; charset=UTF-8');
            header('X-Powered-By: YiMiUgo;'); // 隐藏 PHP 版本 X-Powered-By: PHP/5.5.9
            header('Server: Nginx');
        }

        //错误处理
        $dispatcher->setErrorHandler([get_class($this), 'error_handler']);
        //添加路由协议
        $dispatcher->getRouter()->addConfig((new Yaf_Config_Ini(APP_PATH.'/conf/routes.ini'))->routes);

        $dispatcher->registerPlugin(new RoutePlugin());

        //注册本地类
      //  Yaf_Loader::getInstance()->registerLocalNameSpace(['misc']);

        // 加载数据库
        $database_config = new Yaf_Config_Ini(APP_PATH.'/conf/database.ini', ENV);
       
        Yaf_Registry::set('database', $database_config);
       
        CoreInit::ob_start();
    }

    /*
     * Custom error handler.
     *
     * Catches all errors (not exceptions) and creates an ErrorException.
     * ErrorException then can caught by Yaf\ErrorController.
     *
     * @param integer $errno the error number.
     * @param string $errstr the error message.
     * @param string $errfile the file where error occured.
     * @param integer $errline the line of the file where error occured.
     *
     * @throws ErrorException
     */
    public static function error_handler($errno, $errstr, $errfile, $errline)
    {
        // 防止死循环
        $error_type = array(
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parsing Error', // uncatchable
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error', // uncatchable
            E_CORE_WARNING => 'Core Warning', // uncatchable
            E_COMPILE_ERROR => 'Compile Error', // uncatchable
            E_COMPILE_WARNING => 'Compile Warning', // uncatchable
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Runtime Notice',
            //E_RECOVERABLE_ERRROR => 'Catchable Fatal Error'
        );
        $error_str = isset($error_type[$errno]) ? $error_type[$errno] : 'Unknonw';
    
        $msg = "[$error_str] : $errstr in File $errfile, Line: $errline";
    
        if (DEBUG) {
            throw new Exception($msg);
        } else {
            if ($errno == E_NOTICE || $errno == E_USER_ERROR || $errno == E_USER_NOTICE || $errno == E_USER_WARNING || $errno == E_STRICT) {
                // 继续执行。
               // @$_SERVER['notice_error'] .= $msg;
            } else {
                //$msg = preg_replace('# \S*[/\\\\](.+?\.php)#', ' \\1', $msg);
                if (CoreInit::gpc('ajax', 'R')) {
                    CoreInit::ob_clean();
                    //$msg = preg_replace('#[\\x80-\\xff]{2}#', '?', $msg);// 替换掉 gbk， 否则 json_encode 会报错！
                    $msg = CoreInit::json_encode(array('server_error' => $msg));
                }
                Log::write($msg);
                exit($msg);
            }
        }

        return 0;
    }
}
