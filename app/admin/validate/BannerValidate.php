<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/11
 * Time: 14:34
 */

namespace app\admin\validate;

use think\Validate;
class BannerValidate extends Validate
{
    protected $rule = [
        'title'=>'require|max:25',
        'cover_img'=>'require'
    ];

}