<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/21
 * Time: 10:48
 */

namespace app\api\controller;


use app\admin\model\CaseModel;

class CaseController extends Base
{
    /**
     * 获取案例
     */
    public function index(){
        $page = input('page',1,'intval');
        $len  = input('len',2,'intval');

        $caseModel = new CaseModel();
        $case = $caseModel->where('status',1)->page($page,$len)->select();
        $num = $caseModel->where('status',1)->count(1);

        foreach ($case as $key=>$value){
            $case[$key]['cover_img'] = cmf_get_image_preview_url($value['cover_img']);
            $case[$key]['thumb'] = cmf_get_image_preview_url($value['thumb']);
        }
        return $this->output_success(13111,['list'=>$case,'num'=>$num],'获取案例成功');

    }

    public function read(){
        $id = input('id',0,'intval');

        if(empty($id)) return $this->output_error(13000,'id必须');

        $caseModel = new CaseModel();
        $case = $caseModel->where(['id'=>$id,'status'=>1])->find();
        $case['cover_img'] = cmf_get_image_preview_url($case['cover_img']);
        $case['thumb'] = cmf_get_image_preview_url($case['thumb']);

        return $this->output_success(13112,$case,'获取案例详情成功');
    }
}