<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/21
 * Time: 11:19
 */

namespace app\api\controller;

use app\admin\model\RecruitModel;

class RecruitController extends BaseController
{
    public function index(){
        $page = input('page',1,'intval');
        $len  = input('len',8,'intval');
        $recruitModel = new RecruitModel();
        $recruits = $recruitModel->order('list_order ASC')->page($page,$len)->select();

        return $this->output_success(13111,$recruits,'获取招聘信息成功');
    }

    public function read(){
        $id = input('id',0,'intval');

        if(empty($id)) return $this->output_error(13000,'id必须');

        $recruitModel = new RecruitModel();
        $recruit = $recruitModel->where(['id'=>$id])->find();

        return $this->output_success(13111,$recruit,'获取招聘信息成功');
    }
}