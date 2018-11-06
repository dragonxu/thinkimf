<?php
/**
 * Created by PhpStorm.
 * User: chenjianhua
 * Date: 2018/4/26
 * Time: 上午2:12
 */
namespace app\common;
use think\Controller;
use think\Request;
class Base extends Controller{

    public function __construct(Request $request)
    {
        parent::__construct();
        /*if ($request->isMobile()) {
            $this->view->config('view_path','themes/default/mobile/' . $request->module() . "/");
        } else {
        }*/
        $this->view->config('view_path','themes/default/web/' . $request->module() . "/");
    }

    //返回json数据
    public function json($re)
    {
        if($re){
            header('Content-type: application/json');
            exit(json_encode($re,JSON_UNESCAPED_UNICODE));
        }else{
            exit('response data not correct!');
        }
    }
}