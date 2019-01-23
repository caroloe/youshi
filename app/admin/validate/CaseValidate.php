<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2019/1/11
 * Time: 14:34
 */

namespace app\admin\validate;

use think\Validate;
class  CaseValidate extends Validate
{
    protected $rule = [
        'title'=>'require',
        'cover_img'=>'require',
        'thumb'=>'require',
        'cooperative_units'=>'require'
    ];

    protected $message = [
        'title.require' => '请输入文章标题！',
        'cooperative_units.require' => '请输入合作单位！',
        'thumb.require' => '首页展示图不能为空！',
        'cover_img.require' => '封面图不能为空！',
    ];

}