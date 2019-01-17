<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/11
 * Time: 14:34
 */

namespace app\admin\validate;

use think\Validate;
class RecruitValidate extends Validate
{
    protected $rule = [
        'position'=>'require|max:25'
    ];

}