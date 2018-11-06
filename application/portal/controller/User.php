<?php

namespace app\portal\controller;

use app\admin\model\UserModel;
use think\Controller;
use app\portal\controller\Base;

//用户个人中心
class User extends Base
{
    //个人中心首页
    //找回密码
    //密码绑定
    //手机绑定
    //个人支付
    //个人指数
    //个人区块链
    //我的设备
    //我的二维码
    //第三方绑定
    //我的族谱
    //我的好友
    /**
     * @desc 我的信息
     * @return mixed
     */
    public function index()
    {
        $this->assign('title', '个人中心');
        $this->assign('ak', config('baidu_map_ak'));
        return $this->fetch('user/index');
    }

    /**
     * @desc 我的信息，myinfo
     */
    public function info()
    {
        if ($this->request->isPost()) {

        } else {
            $this->assign('title', '我的信息');
            $userInfo = [];
            /* $this->assign('description','');
             $this->assign('keywords','');*/
            if(session('')){
                $userModel  = new UserModel();
                $userInfo = $userModel->where(['id'=>session('')['userid']])
                    ->find()->toArray();
                if($userInfo){
                    $userInfo['img'] = 'http://'.$_SERVER['SERVER_NAME'].'/static/uploads/'.$userInfo['img'];
                }
            }
            return $this->fetch('user/setting',['user'=>$userInfo]);
        }
    }

    /**
     * 我的设备
     * @return mixed
     */
    public function myDevice()
    {
        if ($this->request->isPost()) {
        } else {
            $this->assign('ak', config('baidu_map_ak'));
            $this->assign('title', '我的设备');
            /* $this->assign('description','');
             $this->assign('keywords','');*/
            return $this->fetch('user/device');
        }
    }

    /**
     * @desc 我的数据
     */
    public function myMessage()
    {
        if ($this->request->isPost()) {
        } else {
            $this->assign('title', '我的数据');
            /* $this->assign('description','');
             $this->assign('keywords','');*/
            return $this->fetch('user/message');
        }
    }

    /**
     * @desc 设置
     * @return mixed
     */
    public function setting()
    {
        if ($this->request->isPost()) {

        } else {
            $this->assign('title', '设置');
            $userInfo = [];
            /* $this->assign('description','');
             $this->assign('keywords','');*/
            if(session('')){
                $userModel  = new UserModel();
                $userInfo = $userModel->where(['id'=>session('')['userid']])
                    ->find()->toArray();
                if($userInfo){
                    $userInfo['img'] = 'http://'.$_SERVER['SERVER_NAME'].'/static/uploads/'.$userInfo['img'];
                }
            }
            return $this->fetch('user/setting',['user'=>$userInfo]);
        }
    }
}
