<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/23
 * Time: 16:59
 */

namespace app\api\controller;

use app\admin\model\ContactModel;

class ContactController extends Base
{
    public function index(){
        $contactModel = new ContactModel();
        $contact = $contactModel->where(['status'=>1])->select();

        return $this->output_success(12222,$contact,'获取联系我们信息成功');

    }


}