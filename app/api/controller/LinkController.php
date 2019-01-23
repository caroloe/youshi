<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/23
 * Time: 11:40
 */


namespace app\api\controller;

use app\admin\model\LinkModel;

class LinkController extends Base
{
    //获取友情链接成功
    public function index(){
        $linkModel = new LinkModel();
        $links     = $linkModel->where('status',1)->select();
        foreach ($links as $key=>$item){
            $links[$key]['image'] = cmf_get_image_preview_url($item['image']);
        }

        return $this->output_success(11111,$links,'获取友情链接成功');
    }
}
