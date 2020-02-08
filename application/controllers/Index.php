<?php

/**
 * 默认controller层
 * author: NuoCoo
 * creatTime: 15/9/30 上午9:48
 * description:
 * php版本只支持5.3以上.
 */
class IndexController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    /**
     * 接口目录.
     */
    public function indexAction()
    {
        Yaf_Dispatcher::getInstance()->disableView();
        $config = Yaf_Application::app()->getConfig();
        $this->getView()->setScriptPath($config->application->template.'/template/views');
        $this->getView()->assign('top', '321321');
        $this->getView()->assign('data', '3213');
        $this->getView()->display('index.phtml');
    }

    public function formsAction()
    {
        Yaf_Dispatcher::getInstance()->disableView();
        $config = Yaf_Application::app()->getConfig();
        $this->getView()->setScriptPath($config->application->template.'/template/views');
        $this->getView()->display('forms.phtml');
    }
}
