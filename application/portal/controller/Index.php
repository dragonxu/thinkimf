<?php

namespace app\portal\controller;

use const APP_ROOT;
use const SOURCE_ROOT;
use think\Controller;
use app\portal\controller\Base;

class Index extends Base
{
    public function index()
    {
        //判断是否已经安装
        if (!is_file(SOURCE_ROOT . '/data/install/install.lock')) {
            //Container::get('app');
            header("Location:/install/index/index");
            exit;
        }
//        $this->json([
//            'code' => 200,
//            'msg' => 'welcome',
//            'data' => []
//        ]);
        return $this->fetch('index/index',[
            'title'=>'ThinkIMF基于PHP7与ThinkPHP的管理框架'
        ]);
    }

    public function aboutus()
    {
        $this->assign('title', '关于我们');
        return $this->fetch('index/about-us');
    }

    public function projecttimeline()
    {
        $this->assign('title', '项目时间线');
        return $this->fetch('index/project-timeline');
    }
}
