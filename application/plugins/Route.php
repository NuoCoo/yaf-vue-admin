<?php
/**
 * 路由插件
 * 使所有/module/controller/index 访问方式改为 /module/controller
 * @author : wuhang
 * @date : 2014-09-10
 */
class RoutePlugin extends Yaf_Plugin_Abstract {

    /*在路由之前触发*/
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) 
    {

    }

    /*路由结束之后触发*/
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        if(!empty(Yaf_Application::app()->getConfig()->get('modules.state'))){
            $route_mode = json_decode(Yaf_Application::app()->getConfig()->get('modules.config'), true);
            /*获取请求的域名前缀*/
            $http_host = explode('.',$_SERVER['HTTP_HOST'])[0];
	        if(empty($http_host)){
                exit('请求错误');
            }
            if(!isset($route_mode[$http_host])){
                exit('请求错误');
            }

            $controller = $request->getControllerName();

            $request_url = array_filter(explode('/', $_SERVER['REQUEST_URI']));
            if($http_host == 'api' && in_array('web2', $request_url)){
                $request->setModuleName ('web2');
            } else {
                $request->setModuleName ($route_mode[$http_host]);
            }
            $action = $request->getActionName();
            $module = $route_mode[$http_host];
            if(strtolower($module) == "index"){
                if(file_exists(APP_PATH.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller.'.php')){
                    exit('请求错误');
                }
                $request->setControllerName(ucwords($action));
                $request->setActionName("index");
            }
        }

    }

    /*	分发循环开始之前被触发	*/
    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {

    }

    /*	分发之前触发*/
    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {

    }

    /*分发结束之后触发*/
    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) 
    {

    }

    /*分发循环结束之后触发*/
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {

    }

}
