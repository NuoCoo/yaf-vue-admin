<?php

use Illuminate\Database\Database;
use Master\Utils\Misc;
use Master\Utils\Response;
use Master\Utils\Request;

class FrameworkController extends AppParentsController
{
    /**
     * 析构函数.
     */
    public function init()
    {
        parent::init();
    }

    public function getHeaderColumnAction()
    {
        $db = Database::getInstance();
        $menu_lists = $db->selects('admin_column', [], 'id,name,url,icon,parent_id', ['sort' => 'ASC']);

        foreach ($menu_lists as $key => $val) {
            $menu_lists[$key]['column_id'] = $val['id'];
        }
       
        Response::json('200', 'success',  Misc::recursion($menu_lists));
    }

    public function getLeftColumnAction()
    {
        $column_id = $this->getRequest()->getPost('column_id');
      
        $db = Database::getInstance();
        $menu_lists = $db->selects('admin_menu', ['column_id'=>$column_id], 'id,name,url,icon,parent_id,column_id', ['sort' => 'ASC']);

        Response::json('200', 'success', Misc::recursion($menu_lists));
    }

    public function getHeaderColumnBakAction()
    {
        Response::json('200', 'success', [
            ['id' => '1', 'name' => '车位管理', 'url' => '', 'icon' => '', 'children' => [
                ['id' => '11', 'name' => '订单管理', 'url' => '', 'icon' => '', 'children' => ''],
                ['id' => '12', 'name' => '订单管理', 'url' => '', 'icon' => '', 'children' => ''],
                ['id' => '13', 'name' => '订单管理', 'url' => '', 'icon' => '', 'children' => ''],
                ['id' => '14', 'name' => '订单管理', 'url' => '', 'icon' => '', 'children' => ''],
            ]],
            ['id' => '2', 'name' => '订单管理', 'url' => '', 'icon' => '', 'children' => [
                ['id' => '21', 'name' => '订单管理', 'url' => '', 'icon' => '', 'children' => ''],
                ['id' => '22', 'name' => '订单管理', 'url' => '', 'icon' => '', 'children' => ''],
                ['id' => '23', 'name' => '订单管理', 'url' => '', 'icon' => '', 'children' => ''],
            ]],
            ['id' => '3', 'name' => '商品管理', 'url' => '', 'icon' => '', 'children' => ''],
            ['id' => '4', 'name' => '客服管理', 'url' => '', 'icon' => '', 'children' => ''],
            ['id' => '5', 'name' => '营销管理', 'url' => '', 'icon' => '', 'children' => [
                ['id' => '51', 'name' => '地区营销', 'url' => '', 'icon' => '', 'children' => ''],
                ['id' => '52', 'name' => '车位营销', 'url' => '', 'icon' => '', 'children' => ''],
                ['id' => '53', 'name' => '人员营销', 'url' => '', 'icon' => '', 'children' => ''],
            ]],
            ['id' => '6', 'name' => '系统设置', 'url' => '/wmore/system/users', 'icon' => '', 'children' => ''],
        ]);
    }
}
