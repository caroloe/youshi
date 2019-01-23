<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/11
 * Time: 14:34
 */

namespace app\admin\validate;

use think\Validate;
class  ContactValidate extends Validate
{
    protected $rule = [
        'title'=>'require',

    ];

    protected $message = [
        'title.require' => '请输入标题！',

    ];

}