<?php if (!defined('APP_PATH')) exit('No direct script access allowed');

/**
 * app基类controller
 * author: nuocoo
 * creatTime: 19/12/01 下午16:13
 * description:
 * php版本只支持5.3以上
 */
class AppParentsController extends Yaf_Controller_Abstract {

    /*
     * 所有配置信息.
     */
    protected $config;

    /*
     * redis
     */
    protected $redis;
    /**
     * 初始函数
     */
    public function init() {
        // Assign application config file to this controller
        $this->config = Yaf_Application::app()->getConfig();
        //redis缓存
        $this->redis = Yaf_Registry::get('redis');
       
    }
}