<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/21
 * Time: 9:51
 */

namespace app\api\controller;

use app\admin\model\PortalCategoryModel;
use app\admin\model\PostModel;

class AboutUsController extends BaseController
{
    /**
     * 首页获取公司简介
     */
    public function index(){
        $postModel = new PostModel();
        $post = $postModel
            ->alias('p')
            ->join('portal_category_post r','r.post_id=p.id')
            ->where(['r.category_id'=>3])
            ->find();


        return $this->output_success(11222,$post,'获取简介成功');
    }


    //获取关于我们
    public function getInfo(){
        $id = input('param.id',0,'intval');

        if(empty($id)) return $this->output_error(11000,'id必须');

        $postModel = new PostModel();
        if($id == 4){
            $post = $postModel->alias('p')->join('portal_category_post r','r.post_id=p.id')->field('p.post_title,p.post_content')->select();
        }else{
            $post = $postModel->alias('p')->join('portal_category_post r','r.post_id=p.id')->field('p.post_title,p.post_content')->find();
        }

        return $this->output_success(11223,$post,'获取信息成功');

    }
}