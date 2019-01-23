<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/23
 * Time: 15:45
 */

namespace app\admin\controller;


use app\admin\model\ContactModel;
use app\admin\validate\ContactValidate;
use cmf\controller\AdminBaseController;

class ContactController extends AdminBaseController
{
    //获取联系我们信息
    public function index()
    {
        $contactModel = new ContactModel();
        $contacts = $contactModel->paginate(10);

//        $contact->appends($param)

        $this->assign('contacts', $contacts);
        $this->assign('page', $contacts->render());

        return $this->fetch();

    }

    /**
     * 添加文章
     * @adminMenu(
     *     'name'   => '添加文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加文章提交
     * @adminMenu(
     *     'name'   => '添加文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $data   = $this->request->param();

        if($data['title'] == '') return $this->error('banner名称必须');

        $contactModel = new ContactModel();
        $result    = $contactModel->allowField(true)->save($data);
        if ($result === false) {
            $this->error($contactModel->getError());
        }

        $this->success("添加成功！", url("contact/index"));

    }


    /**
     * 编辑文章
     * @adminMenu(
     *     'name'   => '编辑文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $contactModel = ContactModel::get($id);
//        var_dump($bannerModel);die;
        $this->assign('contact', $contactModel);
        return $this->fetch();
    }

    /**
     * 编辑文章提交
     * @adminMenu(
     *     'name'   => '编辑文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {

        if ($this->request->isPost()) {
            $data   = $this->request->param();

            $validate = new ContactValidate();
            if ($validate->check($data) !== true) {
                $this->error($validate->getError());
            }

            //编辑文章
            $postModel = new ContactModel();
            $postModel->allowField(true)->isUpdate(true)->save($data,['id'=>$data['id']]);


            $this->success('保存成功!','contact/index');

        }
    }

    /**
     * 文章删除
     * @adminMenu(
     *     'name'   => '文章删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {

        $param           = $this->request->param();
        $id = $param['id'];

        $contactModel = new ContactModel();
        $contactModel->where(['id'=>$id])->delete();


        $this->success('删除成功');

    }



    /**
     * 文章排序
     * @adminMenu(
     *     'name'   => '文章排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('portal_category_post'));
        $this->success("排序更新成功！", '');
    }

    public function move()
    {

    }

    public function copy()
    {

    }
    public function area_venues(){
        $area_id=$this->request->param('area_id',0,'intval');
        $userModel= new UserModel();
        $venues=$userModel->area_venues($area_id);
        if(!empty($venues)){
            echo json_encode(['status'=>1,'data'=>$venues,'message'=>'场馆获取成功']);exit;
        }else{
            echo json_encode(['status'=>1,'data'=>'','message'=>'无场馆']);exit;
        }
    }


    /**
     * 文章库
     */
    public function article_lib($param,$id){

        $categoryId = $this->request->param('category', 0,'intval');
        $all_status = $this->request->param('all_status', 0,'intval');
        $postService = new PostService();
        $portalCategoryModel = new PortalCategoryModel();

//        $haveChild = false;
//        if(!isset($param['category']) || $param['category'] == 0 || $param['category'] == $id){
//            $param['category'] = $id;
//        }


        $data = $postService->ArticleList($param,false,$all_status);

        $categoryTree = $portalCategoryModel->adminCategoryTree($categoryId, 0, $id);

        //区域列表
        $areaModel = new AreaModel();
        $area_sid = isset($param['area_id'])?$param['area_id']:'';
        $areas = $areaModel->adminAreaTree($area_sid);

        $venueModel = new VenueModel();
        $venues = $venueModel->where(['id' => ['in', UserModel::getCurrentVenue2()]])->order(['list_order' => 'asc'])->select()->toArray();
        $venue_id = isset($param['venue'])?$param['venue']:0;
        $myv = '';
        foreach ($venues as $v) {
            $select = $v['id'] ==$venue_id ? 'selected' : '';
            $myv .= "<option value='" . $v['id'] . "' " . $select . ">" . $v['name'] . "</option>";
        }

        $page = $data->render();
        $this->assign('all_status', $all_status);
        $this->assign('areas', $areas);
        $this->assign('venues', json_encode($venues));
        $this->assign('page',$page);
        $this->assign('articles',$data);
        $this->assign('id', $id);
        $this->assign('category_tree',$categoryTree);
        $this->assign('venue_sid',json_encode(isset($param['venue'])?$param['venue']:''));
        $this->assign('my_venue', $myv);

        return $this->fetch();
    }


    public function previewHandler()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $body = $data['content'];
            $redis = Redis::getRedis();
            $redis->set("preview" , $body);
            echo json_encode(
                [
                    'status' => 1,
                    'data' => '/portal/category/preview',
                    'message' => '场馆获取成功'
                ]
            );exit;
        }
    }

}
