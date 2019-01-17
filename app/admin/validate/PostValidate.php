<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/15
 * Time: 16:44
 */


namespace app\admin\validate;

use think\Validate;
class PostValidate extends Validate
{
    protected $rule = [
//        'post_title'=>'require|max:25',
        'post_content'=>'require',
    ];

}