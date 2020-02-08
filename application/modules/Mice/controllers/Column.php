<?php

use Illuminate\Database\Database;
use Master\Utils\Misc;
use Master\Utils\Response;
use Master\Utils\Request;

class ColumnController extends AppParentsController
{
    /**
     * 析构函数.
     */
    public function init()
    {
        parent::init();
    }

    public function listsAction()
    {
        if($this->getRequest()->isPost()){
            $model = Database::getInstance();
            $lists = $model->selects('admin_column', [], '*', ['sort' => 'ASC']);
            Response::json('200', 'success', [
                'rows'  => Misc::recursion($lists),
                'total' => 10
            ]);
        } 
    }

    public function insertAction()
    {
        if($this->getRequest()->isPost()){
            $insert['parent_id'] = $this->getRequest()->getPost ('parent_id', 0);
            $insert['name'] = $this->getRequest()->getPost ('name', '');
            $insert['url'] = $this->getRequest()->getPost ('url', '');
            $insert['icon'] = $this->getRequest()->getPost ('icon', '');
            $insert['status'] = $this->getRequest()->getPost ('status', '');
            $insert['sort'] = $this->getRequest()->getPost ('sort', '');
            $insert['status'] = $insert['status'] == 'true' ? 1 : 0;
            $model = Database::getInstance();
            $lists = $model->inserts('admin_column', $insert);
            Response::json('200', '菜单添加成功！');
        } 
    }

    public function updateAction()
    {
        if($this->getRequest()->isPost()){
            $menu_id =  $this->getRequest()->getPost ('id', 0);
            $update['parent_id'] = $this->getRequest()->getPost ('parent_id', 0);
            $update['name'] = $this->getRequest()->getPost ('name', '');
            $update['url'] = $this->getRequest()->getPost ('url', '');
            $update['icon'] = $this->getRequest()->getPost ('icon', '');
            $update['status'] = $this->getRequest()->getPost ('status', '');
            $update['sort'] = $this->getRequest()->getPost ('sort', '');
            $update['status'] = $update['status'] == 'true' ? 1 : 0;
            $model = Database::getInstance();
            $model->updates('admin_column', ['id'=>$menu_id],  $update);
            Response::json('200', '菜单编辑成功！');
        } 
    }

    public function removeAction(){
        if($this->getRequest()->isPost()){
            $menu_id =  $this->getRequest()->getPost ('id', 0);
            
            $model = Database::getInstance();
            $lists = $model->removes('admin_column',['id'=>$menu_id]);
            Response::json('200', '菜单删除成功！');
        } 
    }


    public function getColumnParentsAction(){
        $model = Database::getInstance();
        $lists = $model->selects('admin_column', ['parent_id'=>0, 'status'=>1], 'id(value),name(label)', ['sort' => 'ASC']);
        Response::json('200', 'success', $lists);
    }
}
