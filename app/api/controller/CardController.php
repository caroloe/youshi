<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/6
 * Time: 9:49
 */
namespace app\api\controller;

use think\Db;
class CardController extends Base
{
    public function index(){
        $res = Db::name('card')->select();
        return $this->output_success(10010,$res,'获取储值卡列表成功');
    }


    //新增储值卡
    public function create(){
        $price = input('price',100,'intval');
        $money = input('money',100,'intval');
        $name  = input('name','五折','trim');

        if($name == ''){
            return $this->output_error(10010,'储值卡名不能为空');
        }
        if($money == 0){
            return $this->output_error(10012,'卡内金额不能为空');
        }
        if($price == 0){
            return $this->output_error(10013,'价格不能为空');
        }

        //添加
        $data = ['name'=>$name,'price'=>$price,'money'=>$money];
        Db::name('card')->insert($data);

        return $this->output_success(10011,'','添加储蓄卡成功');
    }

    public function show($id){
        $info = Db::name('card')->where('id',$id)->find();
        return $this->output_success(10012,$info,'获取储值卡信息成功');
    }

    public function delete($id){
        Db::name('card')->where('id',$id)->delete();
        return $this->output_success(10012,'','储值卡删除成功');
    }

    public function update(){
        $price = input('price',-1);
        $money = input('money',-1);
        $name  = input('name','');
        $id    = input('id',0);

        $data = [];
        if($price != -1){
            $data['price'] = $price;
        }
        if($money != -1){
            $data['money'] = $money;
        }
        if($name != ''){
            $data['name'] = $name;
        }
        if($id == 0){
            return $this->output_error(10100,'id必须');
        }
        Db::name('card')->where('id',$id)->update($data);
        return $this->output_success(10012,'','储值卡更新成功');
    }


}
