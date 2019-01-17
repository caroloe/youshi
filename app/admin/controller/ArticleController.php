<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;


use app\admin\model\PostModel;
use app\admin\model\RelaModel;
use app\admin\validate\PostValidate;
use cmf\controller\AdminBaseController;
use app\admin\model\UserModel;
use app\portal\model\PortalPostModel;
use app\portal\model\PortalCategoryModel;
use app\portal\service\PostService;
use think\Db;
use app\admin\model\ThemeModel;
use redis\Redis;

class ArticleController extends AdminBaseController
{
    private $allCid;
    /**
     * 文章列表
     * @adminMenu(
     *     'name'   => '文章管理',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();
        $id = $param['id'];

        $thisNode = PortalCategoryModel::get($id);
        if($thisNode == null){
            $this->error('文章分类不存在或非文章类栏目');
        }

        if($thisNode['show_way'] == 1){
            //单个文章
            $postModel = new PostModel();
            $article = $postModel
                ->alias('a')
                ->join('__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id')
                ->where('b.category_id',$id)->find();
            $this->assign('category_id', $id);
            $this->assign('article',$article);
            $this->assign('category',$thisNode['name']);
            return $this->fetch('article/single');
        }else{
            //文章列表
            $where = [];
            if(isset($param['keyword']) && $param['keyword'])  $where['post_title'] = ['like','%$keyword%'];
            $postModel = new PostModel();
            $articles = $postModel
                ->alias('a')
                ->join('__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id')
                ->where('b.category_id',$id)
                ->where($where)
                ->field('a.*,b.list_order,b.id as post_category_id,b.status')
                ->paginate(10);

            $this->assign('category_id', $id);
            $this->assign('category', $thisNode['name']);
            $this->assign('articles',$articles);
            $this->assign('page',$articles->render());

            return $this->fetch('article/index');

        }

        /*$categoryId = $this->request->param('category', 0,'intval');
        $postService = new PostService();

        $portalCategoryModel = new PortalCategoryModel();



        $data = $postService->adminArticleList($param, $haveChild,$all_status);

        $data->appends($param);


        $categoryTree = $portalCategoryModel->adminCategoryTree($categoryId, 0, $id);
        $categoryTree = '<option value="' . $id . '">' . $thisNode->name . '</option>'. $categoryTree;
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('all_status',$all_status);
        $this->assign('articles', $data->items());

        $this->assign('category_tree', $categoryTree);
        $this->assign('category', $categoryId);
        $this->assign('page', $data->render());
        $this->assign('id', $id);


        return $this->fetch();*/

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
        $category_id = $this->request->param('category_id');
        $thisNode = PortalCategoryModel::get($category_id);
        if($thisNode == null){
            $this->error('文章分类不存在或非文章类栏目');
        }

        $this->assign('category',$thisNode['name']);
        $this->assign('category_id',$category_id);
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
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $category_id = $this->request->param('category_id');

            if(isset($data['post_content']) && $data['post_content']){
                $data['post_content'] = htmlspecialchars_decode($data['post_content']);
            }

            //添加
            //添加文章
            $postModel = new PostModel();
            $res = $postModel->allowField(true)->save($data);

            if($res){
                //添加关联
                $insert_data = [
                    'post_id'=>$postModel->id,
                    'category_id'=>$category_id,
                    'status'=>isset($data['status']) ? $data['status'] : 1
                ];
                $categoryModel = new RelaModel();
                $categoryModel->allowField(true)->save($insert_data);
                $this->success('添加成功','/admin/article/'.$category_id);
            }


//            hook('portal_admin_after_save_article', $hookParam);
        }

    }

    public function save(){
        $data = $this->request->param();
        $category_id = $this->request->param('category_id');
        $id = isset($data['id']) ? $data['id'] : 0;
        $data['post_content'] = isset($data['post_content']) ? htmlspecialchars_decode($data['post_content']) : '';

        $validate = new PostValidate();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $thisNode = PortalCategoryModel::get($category_id);
        if($thisNode == null){
            $this->error('文章分类不存在或非文章类栏目');
        }


        $postModel = new PostModel();
        if($id == 0){
            //添加
            //添加文章
            $res1 = $postModel->allowField(true)->save($data);
            if($res1){
                //添加关联
                $insert_data = [
                    'post_id'=>$postModel->id,
                    'category_id'=>$category_id,
                    'status'=>isset($data['status']) ? $data['status'] : 1
                ];
                $relaModel = new RelaModel();
                $relaModel->save($insert_data);

                $this->success('添加成功');
            }
        }else{
            //修改
            $postModel->allowField(true)->isUpdate(true)->save($data,['id'=>$data['id']]);
            $this->success('修改成功');
        }

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
        $cid = $this->request->param('cid',0, 'intval');
        $id = $this->request->param('id', 0, 'intval');

        $postModel = new PostModel();
        $article = $postModel
            ->alias('a')
            ->join('__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id')
            ->where('a.id',$id)
            ->field('a.*,b.list_order')
            ->find();

        $this->assign('article',$article);
        $this->assign('cid', $cid);
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

            $validate = new PostValidate();
            if ($validate->check($data) !== true) {
                $this->error($validate->getError());
            }
            $data['post_content'] = htmlspecialchars_decode($data['post_content']);

            //编辑文章
            $postModel = new PostModel();
            $postModel->allowField(true)->isUpdate(true)->save($data,['id'=>$data['id']]);


            $this->success('保存成功!','article/'.$data['category_id']);

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


        $relaModel = new RelaModel();
        $relaModel->where(['post_id'=>$id])->delete();
        $postModel = new PostModel();
        $postModel->where(['id'=>$id])->delete();

        $this->success('删除成功');

    }

    public function recovery(){
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result       = $portalPostModel->where(['id' => $id])->find();
            $data         = [
                'object_id'   => $result['id'],
                'create_time' => time(),
                'table_name'  => 'portal_post',
                'name'        => $result['post_title'],
                'user_id'=>cmf_get_current_admin_id()
            ];
            $resultPortal = $portalPostModel
                ->where(['id' => $id])
                ->update(['delete_time' => 0]);
            if ($resultPortal) {
                Db::name('portal_category_post')->where(['post_id'=>$id])->update(['status'=>1]);
                Db::name('portal_tag_post')->where(['post_id'=>$id])->update(['status'=>1]);
//                Db::name('recycleBin')->insert($data);
            }
            $this->success("恢复成功！", '');

        }

        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $recycle = $portalPostModel->where(['id' => ['in', $ids]])->select();
            $result  = $portalPostModel->where(['id' => ['in', $ids]])->update(['delete_time' => 0]);
            if ($result) {
                Db::name('portal_category_post')->where(['post_id' => ['in', $ids]])->update(['status'=>1]);
                Db::name('portal_tag_post')->where(['post_id' => ['in', $ids]])->update(['status'=>1]);
                foreach ($recycle as $value) {
                    $data = [
                        'object_id'   => $value['id'],
                        'create_time' => time(),
                        'table_name'  => 'portal_post',
                        'name'        => $value['post_title'],
                        'user_id'=>cmf_get_current_admin_id()
                    ];
//                    Db::name('recycleBin')->insert($data);
                }
                $this->success("恢复成功！", '');
            }
        }

    }

    /**
     * 文章发布
     * @adminMenu(
     *     'name'   => '文章发布',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章发布',
     *     'param'  => ''
     * )
     */
    public function publish()
    {
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['post_status' => 1, 'published_time' => time()]);

            $this->success("发布成功！", '');
        }

        if (isset($param['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['post_status' => 0]);

            $this->success("取消发布成功！", '');
        }

    }

    /**
     * 文章置顶
     * @adminMenu(
     *     'name'   => '文章置顶',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章置顶',
     *     'param'  => ''
     * )
     */
    public function top()
    {
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');
            $portalCategoryModel = new PortalCategoryModel();
            $cids = $portalCategoryModel->getTreeIds($this->allCid);
            $checkedId = $portalPostModel
                ->alias('a')
                ->join('__PORTAL_CATEGORY_POST__ b', 'a.id=b.post_id')
                ->where(['is_top' => 1, 'b.category_id'=>['in', $cids], 'delete_time'=>0])
                ->group('a.id')->column('a.id');
            $updateIds = array_flip($checkedId)+array_flip($ids);
            $updateIds = array_keys($updateIds);
            if(count($updateIds) > 2){
                $this->error("最多置顶两条！", '');
            }else{
                $portalPostModel->where(['id' => ['in', $updateIds]])->update(['is_top' => 1]);

                $this->success("置顶成功！", '');
            }

        }

        if (isset($_POST['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['is_top' => 0]);

            $this->success("取消置顶成功！", '');
        }
    }

    /**
     * 文章推荐
     * @adminMenu(
     *     'name'   => '文章推荐',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章推荐',
     *     'param'  => ''
     * )
     */
    public function recommend()
    {
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['recommended' => 1]);

            $this->success("推荐成功！", '');

        }
        if (isset($param['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['recommended' => 0]);

            $this->success("取消推荐成功！", '');

        }
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
