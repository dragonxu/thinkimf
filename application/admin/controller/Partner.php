<?php
/**
 * Created by PhpStorm.
 * User: chenjianhua
 * Date: 2018/3/19
 * Time: 下午10:50
 */

namespace app\admin\controller;

use app\admin\controller\AdminBase;
use app\admin\controller\AuthAdminBase;
use app\admin\model\PartnerModel;


class Partner extends AuthAdminBase
{
    public function list()
    {
        $data = $this->request->request();
        $currentPage = $this->request->request('page', 1);
        $partner = new PartnerModel();
        $page = $currentPage ?? 1;
        $pageSize = $data['pageSize'] ?? 10;
        $offset = ($page - 1) * ($pageSize);
        $count = $partner->count();
        $allPage = ceil($count / $pageSize);
        $list = $partner->limit($offset, $pageSize)
            ->select()->toArray();
        $this->assign("lists", $list);
        $this->assign("pageNation", [
            'total' => $count,
            'currentPage' => $currentPage,
            'allPage' => $allPage
        ]);
        return $this->fetch("partner/list");
    }


    public function edit()
    {
        $data = $this->request->request();
        $id = $data['id'] ?? 1;
        $wasCall = $data['was_call'];
        $f = $partner = PartnerModel::update([
            'id'=>$id,
            'was_call'=>$wasCall
        ]);
        if($f){
            $this->json([
                'code'=>200,
                'msg'=>'ok'
            ]);
        }else{
            $this->json([
                'code'=>200,
                'msg'=>'数据库操作'
            ]);
        }
    }
}