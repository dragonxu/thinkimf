<?php
namespace app\admin\controller;
use think\Controller;

/**
 *
 * ============================================================================
 * [Innovation Framework] Copyright (c) 1995-2028 www.thinkimf.com;
 * 版权所有 1995-2028 陈建华/陈炼/DyoungChen/Dyoung【中国】，并保留所有权利。
 * This is not  free soft ware, use is subject to license.txt
 * 网站地址: http://www.thinkimf.com;
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；作者是一个还要还房贷的码农,请尊重作者的劳动成果,商业用途和技术支持请联系QQ:1367784103。
 * ============================================================================
 * $Author: 陈建华 $
 * $Create Time: 2018/2/9 0009 $
 * email:dyoungchen@gmail.com
 * function:AdminBase.php
 */
use think\Request;
class AdminBase extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct();
       /* if ($request->isMobile()) {
            $this->view->config('view_path','themes/default/mobile/' . $request->module() . "/");
        } else {
        }*/
        $this->view->config('view_path','themes/default/web/' . $request->module() . "/");
    }
}