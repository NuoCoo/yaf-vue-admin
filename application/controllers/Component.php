<?php

use Illuminate\Database\Database;
use Master\Component\Uploads;
use Master\Utils\Response;

/**
 * 默认controller层
 * author: NuoCoo
 * creatTime: 15/9/30 上午9:48
 * description:
 */
class ComponentController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    public function importuploadAction (){
        $upload = Uploads::getInstance();
        $a = Database::getInstance();
        $users = $a->selects('mice_users', ['id[>]'=>0]);
        var_dump($users);exit;
        $upload->simple();
        Response::json(200, 'success', $upload->getFileParams());
    }
}
