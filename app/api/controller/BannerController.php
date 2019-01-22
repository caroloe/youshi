<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/18
 * Time: 14:36
 */
namespace app\api\controller;


use app\admin\model\BannerModel;

class BannerController extends BaseController
{
    public function index()
    {
        $type = input('param.type',1,'intval'); //1pc端   2 wx端
        $bannerModel = new BannerModel();

        $where = [];
        $where['type'] = $type;
        $where['status'] = 1;
        $banners   = $bannerModel->where($where)->order('list_order ASC')->select();
        $banners = $banners->toArray();
//var_dump();die;
        if($banners){
            foreach ($banners as $key=>$item){
                $banner[$key]['cover_img'] = cmf_get_image_preview_url($item['cover_img']);
            }
        }


        return $this->output_success(10011,json($banners),'获取banner成功');

    }





}