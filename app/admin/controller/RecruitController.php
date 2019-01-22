<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/14
 * Time: 11:55
 */

namespace app\admin\controller;

use app\admin\model\RecruitModel;
use cmf\controller\AdminBaseController;

class RecruitController extends AdminBaseController
{

    /**
     * 获取所有职位
     */
    public function index(){
        $param = $this->request->param();

        $recruitModel = new RecruitModel();
        $recruits = $recruitModel->order('list_order ASC')->paginate(10);

        $recruits->appends($param);

        $this->assign('recruits',$recruits->items());
        $this->assign('page',$recruits->render());

        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }


    public function addPost()
    {
        $data      = $this->request->param();
        $data['create_time'] = time();

//        if($data['position'] == '') return $this->error('banner名称必须');
        $bannerModel = new RecruitModel();
        $result    = $bannerModel->validate(true)->allowField(true)->save($data);
        if ($result === false) {
            $this->error($bannerModel->getError());
        }

        $this->success("添加成功！", url("recruit/index"));
    }

    /**
     * 编辑友情链接
     * @adminMenu(
     *     'name'   => '编辑友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $recruit = RecruitModel::get($id);
//        var_dump($bannerModel);die;
        $this->assign('recruit', $recruit);
        return $this->fetch();
    }

    /**
     * 编辑友情链接提交保存
     * @adminMenu(
     *     'name'   => '编辑友情链接提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data      = $this->request->param();
        $recruitModel = new RecruitModel();
        $data['requirement'] = htmlspecialchars_decode($data['requirement']);
        $data['duty'] = htmlspecialchars_decode($data['duty']);
        $result    = $recruitModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($recruitModel->getError());
        }

        $this->success("保存成功！", url("recruit/index"));
    }

    /**
     * 删除友情链接
     * @adminMenu(
     *     'name'   => '删除友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除友情链接',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        RecruitModel::destroy($id);

        $this->success("删除成功！", url("recruit/index"));
    }

    /**
     * 排序
     */
    public function listOrder()
    {
        $recruitModel = new  RecruitModel();
        parent::listOrders($recruitModel);
        $this->success("排序更新成功！");
    }

    /**
     * 显示隐藏
     */
    public function toggle()
    {
        $data      = $this->request->param();
        $recruitModel = new RecruitModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $recruitModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $recruitModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }


    }

}