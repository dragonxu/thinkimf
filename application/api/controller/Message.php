<?php
/**
 * Created by PhpStorm.
 * User: chenjianhua
 * Date: 2018/4/26
 * Time: 上午2:30
 */

namespace app\api\controller;

use app\api\model\Messages;
use app\api\model\Device;
use function var_dump;

class Message extends AuthBase
{

    /*
     * 推送给某一个设备消息
     */
    public function pushTo()
    {
        
    }

    /*
     * 推送给某全部设备消息
     */
    public function pushAll()
    {

    }

    /*
     * 发布一条消息
     */
    public function send()
    {

    }

    /**
     * @desc 我的所有数据列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function messageList()
    {
        $condition['uuid'] = $this->request->get('user_id', session('userid'));
        $device_id = $this->reque3st->get('device_id', false);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 10);
        if ($device_id) {
            $condition['device_id'] = $device_id;
        }
        $result = [
            'code' => 0,
            'msg' => '',
            'count' => '',
            'data' => []
        ];
        $offset = ($page - 1) * $limit;
        $messageModel = new Messages();
        $messageCount = $messageModel->where($condition)
            ->count();
        $messages = Messages::Where($condition)
            ->limit($offset, $limit)
            ->select()->toArray();
        $result['count'] = $messageCount;
        $result['data'] =
            $messages;
        return $this->json($result);
    }

    /**
     * @desc 消息详情
     */
    public function msgDetail()
    {
        $msgId = $this->request->get("id", 0);
        if ($msgId) {
            $condition = ['id' => $msgId];
        };
        $message = Messages::Where($condition)
            ->select()->toArray();
        $result = [
            'code' => 1,
            'msg' => 'ok',
            'data' => $message
        ];
        return $this->json($result);
    }

    /**
     * @desc 将信息删除
     */
    public function markDelete()
    {
        $msgId = $this->request->get("id", 0);
        if ($msgId) {
            $condition = ['id' => $msgId];
        };
        $messageModel = new Messages();


        $result = [
            'code' => 1,
            'msg' => 'ok',
            'data' => $message
        ];
        return $this->json($result);
    }
}