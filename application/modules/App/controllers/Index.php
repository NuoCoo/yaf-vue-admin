<?php
use Web2\Automat\AutomatModel;
use Master\Utils\Response;
use Master\Utils\Request;
class IndexController extends Web2BaseController
{


    /**
     * 析构函数
     */
    public function init()
    {
        parent::init();

    }
    
    public function indexAction(){

    }

    /**
     * 获取离我最近的售货机
     * @param string lat    纬度
     * @param string lng   经度
     * @return void
     */
    public function nearestAction(){
        $latitude = Request::post('lat', 'string');
        $longitude = Request::post('lng', 'string');
        if(empty($latitude) || empty($longitude)){
            Response::json(101, '位置获取失败');
        }
        var_dump($latitude, $longitude);exit;
        $parking_lists = $this->_nuoCoo->selects('jm_auth_group',  ['status'=>1,'type'=>2,'parking'=>1], 'id,title(name),serial_number,site_name,longitude,latitude');
        if(empty($parking_lists)){
            Response::json(102, '没有停车场数据', []);
        }
        foreach ($parking_lists as $key => $val){
            $parking_lists[$key]['distance'] = $this->getDistance($latitude, $longitude, $val['latitude'], $val['longitude']);
        }
        array_multisort(array_column($parking_lists,'distance'),SORT_ASC ,$parking_lists);
        if(empty($parking_lists) || !isset($parking_lists[0])){
            Response::json(103, '没有停车场数据', []);
        }
        Response::json(200, 'ok', $parking_lists[0]);
    }
}
