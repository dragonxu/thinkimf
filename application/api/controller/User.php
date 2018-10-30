<?php
/**
 * Created by PhpStorm.
 * User: chenjianhua
 * Date: 2018/4/26
 * Time: 上午2:30
 */

namespace app\api\controller;

use app\admin\model\App;
use app\admin\model\UserModel;
use function json_decode;
use PHPQRCode\QRcode;
use const JSON_UNESCAPED_UNICODE;
use function json_encode;
use function RedisInstance;
use think\Session;
use function uniqueString;
use function var_dump;

class User extends Base
{

    /*
     * 扫码登录
     */
    public function Qrcode()
    {
        $qrcodeUid = uniqueString(32);
        //给前端
        cookie('qid', $qrcodeUid);
        //给后端服务器
        ob_end_clean();
        header("Content-type: image/png");
        QRcode::png($qrcodeUid, false, 3, 5, 1);

    }

    public function checkScanCode()
    {
        $qrcodeUid = $this->request->request('qid');

        $userInfo = RedisInstance()->get('info' . $qrcodeUid);

        if (is_null($userInfo)) {
            $this->json([
                'code' => 400124,
                'msg' => json_decode($userInfo, true)
            ]);

        } else {

            $userInfoArr = json_decode($userInfo, true);

            session('userid', $userInfoArr['id']);
            session('user_type', $userInfoArr['user_type']);
            session('username', ($userInfoArr['name'] ?? $userInfoArr['id'] ?? $userInfoArr['email'] ?? $userInfoArr['phone'] ?? $userInfoArr['nickname']));
            cookie('userid', $userInfoArr['id']);
            cookie('username', $userInfoArr['name']);

            $this->json([
                'code' => 200,
                'msg' => '扫码登录成功'
            ]);
        }


    }

    public function appQrcodeLogin()
    {
        $uuid = $this->request->post('uuid');
        $qid = $this->request->post('qid');
        if (empty($qid)) {
            $this->json([
                'icode' => 40001,
                'msg' => '缺少参数 qid'
            ]);

        }
        if (empty($uuid)) {
            $this->json([
                'icode' => 40002,
                'msg' => '缺少参数 uuid'
            ]);

        }

        //查找用户信息写入redis
        $userModel = new UserModel();
        $userInfo = $userModel->where(['id' => $uuid])
            ->find();

        $userInfoJson = json_encode($userInfo, JSON_UNESCAPED_UNICODE);

        //userinfo写入用户信息
        $f = RedisInstance()->set('info' . $qid, $userInfoJson);

        if ($f) {
            $this->json([
                'code' => 200,
                'msg' => '扫码成功'
            ]);
        } else {

            $this->json([
                'code' => 40003,
                'msg' => '扫码失败'
            ]);
        }
    }

    /**
     * Notes:
     * @Description:设置個人信息
     * @Author: jerryzst
     * @Date: 2018/10/27 0027
     * @Time: 13:02
     */
    public function setUser()
    {
        $returnVal = ['code' => 101, 'msg' => '失败'];
        $params = $this->request->post();
        if ($params) {
            $must_key = ['username','email','city'];
            if (!$this->checkParam($must_key, $params)) {
                $returnVal['msg'] = '參數有誤';
                return $this->json($returnVal);
            }
            $userModel = new UserModel();
            $userInfo = $userModel->where(['id' => session('')['userid']])
                ->find();
            if (!empty($userInfo)) {
                $userInfo->sex = $params['sex'];
                $userInfo->nickname = $params['username'];
                $userInfo->email = $params['email'];
                $userInfo->city = $params['city'];
                $userInfo->sign = $params['sign'];
                $userInfo->save();
                $returnVal['code'] = 1;
                $returnVal['msg'] = '成功';

            }
        }
        return $this->json($returnVal);
    }

    /**
     * Notes:
     * @Description:上傳圖片
     * @Author: jerryzst
     * @Date: 2018/10/27 0027
     * @Time: 21:38
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setImg()
    {
        $file = request()->file('file');
        $file_path = APP_ROOT.'/static/uploads/';
        $info = $file->move($file_path);
        $reubfo = [];
        if ($info) {
            $reubfo['info'] = 1;
            $reubfo['savename'] = 'http://'.$_SERVER['SERVER_NAME'].'/static/uploads/'.DS.$info->getSaveName();
            $userModel = new UserModel();
            $userInfo = $userModel->where(['id' => session('')['userid']])
                ->find();
            if (!empty($userInfo)) {
                $userInfo->img = $info->getSaveName();
                $userInfo->save();
            }
        } else {
            $reubfo['info'] = 0;
            $reubfo['error'] = $file->getError();
        }
        return $reubfo;
    }

    /**
     * Notes:
     * @Description:修改密碼
     * @Author: jerryzst
     * @Date: 2018/10/27 0027
     * @Time: 20:53
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setPass(){
        $returnVal = ['code' => 101, 'msg' => '失败'];
        $params = $this->request->post();
        if ($params) {
            $must_key = ['nowpass','pass','repass'];
            if (!$this->checkParam($must_key, $params)) {
                $returnVal['msg'] = '參數有誤';
                return $this->json($returnVal);
            }
            $userModel = new UserModel();
            $userInfo = $userModel->where(['id' => session('')['userid']])
                ->find();
            if(md5($params['nowpass']) == $userInfo->password){
                if($params['pass'] == $params['repass']){
                    $userInfo->password = md5($params['pass']);
                    $userInfo->save();
                    $returnVal['code'] = 1;
                    $returnVal['msg'] = '修改成功';
                }else{
                    $returnVal['msg'] = '两次秘密不一样';
                }
            }else{
                $returnVal['msg'] = '原密码错误';
            }
        }
        return $this->json($returnVal);
    }

    /**
     * @description: 验证参数存在并且不为空
     * @return: bool
     */
    private function checkParam($must_key, $param)
    {
        $return = true;
        foreach ($must_key as $key => $value) {
            if (!isset($param[$value]) || empty($param[$value])) {
                $return = false;
                break;
            }
        }
        return $return;
    }
}