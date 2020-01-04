<?php if (!defined('APP_PATH')) exit('No direct script access allowed');

/**
 * 基类controller
 * author: sunfx
 * creatTime: 15/9/30 上午9:48
 * description:
 * php版本只支持5.3以上
 */
class BaseController extends Yaf_Controller_Abstract {
    
    protected $base;
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