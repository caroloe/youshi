<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/7
 * Time: 14:29
 */

namespace app\admin\controller;


use app\admin\model\CaseModel;
use app\portal\model\PortalPostModel;
use cmf\controller\AdminBaseController;


/**
 * Class CaseController
 * @package app\admin\controller
 * 精品案例
 */
class CaseController extends AdminBaseController
{
    public function index()
    {
        $keyword = $this->request->param('keyword');

        $where = [];
        if(!empty($keyword)) $where['title'] = ['like',"%$keyword%"];

        $caseModel = new CaseModel();
        $cases   = $caseModel->where($where)->paginate(10);
        $cases->appends($keyword);

        $this->assign('cases', $cases);
        $this->assign('page', $cases->render());

        return $this->fetch();
    }


    public function add()
    {
        return $this->fetch();
    }


    public function addPost()
    {
        $data      = $this->request->param();
        $data['post']['create_time'] = time();
        $data = $data['post'];
//var_dump($data);die;

        $result = $this->validate($data, 'Case');
        if ($result !== true) {
            $this->error($result);
        }

        $data['content'] = htmlspecialchars_decode($data['content']);
        $caseModel = new CaseModel();
        $result    = $caseModel->allowField(true)->save($data);
        if ($result > 0) {
            $this->success("添加成功！", url("case/index"));
        }else{
            $this->error('添加失败');
        }


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
        $caseModel = CaseModel::get($id);
        $caseModel->content = htmlspecialchars_decode($caseModel->content);
//        var_dump($CaseModel);die;
        $this->assign('case', $caseModel);
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
        $data['content'] = htmlspecialchars_decode($data['content']);

        $CaseModel = new CaseModel();
        $result    = $CaseModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($CaseModel->getError());
        }

        $this->success("保存成功！", url("case/index"));
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
        CaseModel::destroy($id);

        $this->success("删除成功！", url("banner/index"));
    }

    /**
     * 友情链接排序
     * @adminMenu(
     *     'name'   => '友情链接排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $CaseModel = new  CaseModel();
        parent::listOrders($CaseModel);
        $this->success("排序更新成功！");
    }

    /**
     * 友情链接显示隐藏
     * @adminMenu(
     *     'name'   => '友情链接显示隐藏',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接显示隐藏',
     *     'param'  => ''
     * )
     */
    public function toggle()
    {
        $data      = $this->request->param();
        $CaseModel = new CaseModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $CaseModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $CaseModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }


    }

}