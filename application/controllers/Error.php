<?php

use Core\BootstrapRun;

if (!defined('APP_PATH')) exit('No direct script access allowed');

class ErrorController extends BaseController
{

    function errorAction($exception)
    {
        // fallback views path to global when error occured in modules.
        $config = Yaf_Application::app()->getConfig();
        $this->getView()->setScriptPath($config->application->directory . "/views");

        $this->getView()->e = $exception;
        $this->getView()->e_class = get_class($exception);
        $this->getView()->e_string_trace = $exception->getTraceAsString();

        $params = $this->getRequest()->getParams();
        var_dump($params);
        unset($params['exception']);
        $this->getView()->params = array_merge(
            array(),
            $params,
            $this->getRequest()->getPost(),
            $this->getRequest()->getQuery()
        );

        switch ($exception->getCode()) {
            case YAF_ERR_AUTOLOAD_FAILED:
            case YAF_ERR_NOTFOUND_MODULE:
            case YAF_ERR_NOTFOUND_CONTROLLER:
            case YAF_ERR_NOTFOUND_ACTION:
                header('HTTP/1.1 404 Not Found');
                break;
            case 401:
                $this->forward('Index', 'application', 'accessDenied');
                header('HTTP/1.1 401 Unauthorized');
                Yaf_Dispatcher::getInstance()->disableView();
                echo $this->render('accessdenied');
                break;
            default:
                //header("HTTP/1.1 500 Internal Server Error");
                BootstrapRun::dump($exception);
                break;
        }

    }
}
