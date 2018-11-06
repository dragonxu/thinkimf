<?php
namespace app\portal\controller;

use function json_encode;
use const JSON_UNESCAPED_UNICODE;
use think\Controller;
use think\Request;

class Base extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct();
        /*if ($request->isMobile()) {
            $this->view->config('view_path','themes/default/mobile/' . $request->module() . "/");
        } else {
        }*/
        $this->view->config('view_path','themes/default/web/' . $request->module() . "/");
        $this->view->engine->layout('common/default');
        $this->assign('title','');
        $this->assign('description','');
        $this->assign('keywords','');
    }

    public function json($re)
    {
        header('content-type:application/json');
        exit(json_encode($re,JSON_UNESCAPED_UNICODE));
    }

}
