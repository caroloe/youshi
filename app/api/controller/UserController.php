<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/29
 * Time: 17:10
 */
namespace app\api\controller;

use app\admin\service\Curl;
use think\Db;
use think\Validate;
use think\cache\driver\Redis;
class UserController extends Base
{
    private static $appid='wxcbcfb5fc20adeedd';
    private static $appsecret='568d15d6fb0a10b9802a8a49c0c1108f';
    private static $qr_url='https://api.weixin.qq.com/wxa/getwxacode?access_token=';
//    private static $qr_url='https://api.weixin.qq.com/wxa/createwxaqrcode?access_token=';
//    private static $qr_url='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=';
    private static $access_url ='';


    private  static  $key ='QE_CUP';
    //获取所有用户
    public function index(){
        $keyword = input('keyword','');
        $where = [];
        if($ke  yword != ''){
            $where['mobile'] = ['like',"%$keyword%"];
        }
        $res = Db::name('user')->select();
        return $this->output_success(10010,$res,'获取用户成功');
    }

    //创建会员
    public function create(){
        $name = input('name','');
        $mobile = input('mobile','');
        $card_type = input('card_type',0);//会员卡类型


        if($name == ''){
            return $this->output_error(10155,'名字必须');
        }
        if($mobile == ''){
            return $this->output_error(10155,'电话必须 ');
        }if($card_type == ''){
            return $this->output_error(10155,'卡类型必须');
        }


        //用户是否存在
        $user = Db::name('user')->where('mobile',$mobile)->find();
        if($user !== null){
            return $this->output_error(10112,'用户已存在');
        }

        //获取卡内信息
        $card = Db::name('card')->where('id',$card_type)->find();
        if($card == null){
            return $this->output_error(10010,'此类卡不存在');
        }

        //添加
        $data = [
            'name' => $name,
            'mobile' => $mobile,
            'card_type' => $card_type,
            'type' => 2,
            'balance'=> $card['money'],
        ];


        Db::name('user')->insert($data);
        $total_money = Db::name('volume')->where('id',1)->value('total_money');
        Db::name('volume')->where('id', 1)->update(['total_money'=>$card['money']+$total_money]);
        return $this->output_success(10210,'','添加成功');

    }

    //获取用户信息
    public function show(){
        $mobile = input('mobile');
        $user = Db::name('user')->where('mobile', $mobile)->find();
        if($user == null ){
            return $this->output_error(10010,'无此用户');
        }
        return $this->output_success(10010,$user,'获取该用户成功');
    }

    //充值
    public function recharge(){
        $mobile = input('mobile','');
        $money  = input('money',0);
        $card_type   = input('card_type',0,'intval');

        if($mobile == ''){
            return $this->output_error(10111,'电话号码不能为空');
        }
        if($money == 0){
            return $this->output_error(10112,'请输入充值金额');
        }

        $info = Db::name('user')->where('mobile', $mobile)->find();
        $data['balance'] =$money+$info['balance'];
        $data['card_type'] = $card_type? $card_type: $info['card_type'];
        Db::name('user')->where('mobile', $mobile)->update($data);
        $total_money = Db::name('volume')->where('id',1)->value('total_money');
        Db::name('volume')->where('id', 1)->update(['total_money'=>$total_money+$money]);

        return $this->output_success(100113,$data['balance'],'充值成功');


    }


    //消费
    public function consumption(){
        $num =  input('num',1,'intval');
        $price = input('price');
        $mobile = input('mobile');

        if($price == 0){
            return $this->output_error(10016,'请填写价格');
        }

        $info = Db::name('user')->where('mobile', $mobile)->find();
        if($info == null){
            return $this->output_error(10201,'用户不存在');
        }
        if($info['balance'] < $price){
            return $this->output_error(10201,'余额不足');
        }

        Db::name('user')->where('mobile', $mobile)->update(['balance'=>$info['balance']-$price]);

        $spend = $price;
        $con_data = [
            'user_id'=>$info['id'],
            'num'=>$num,
            'spend'=>$spend,
            'create_time'=>time()
        ];
        Db::name('consumption')->insert($con_data);

        return $this->output_success(10211,$info['balance']-$price,'成功');
    }

    public function history(){
        $user_id = input('user_id',0);
        $list = Db::name('consumption')->where('user_id', $user_id)->select();

        return $this->output_success(10010,$list,'获取历史消费成功');
    }

    public function  checkAdmin(){
        $mobile =  input('mobile');
        $key    = input('key','');

        if($mobile == ''){
            return $this->output_error(10010,'手机号不能为空');
        }
        if($key != ''){
            if($key != self::$key){
                return $this->output_error(10015,'验证错误');
            }
            $info = Db::name('user')->where('mobile', $mobile)->find();
            if($info == null){
                Db::name('user')->insert(['mobile'=>$mobile,'type'=>1,'name'=>'管理员']);
                return $this->output_success(10015,'','管理员添加成功');
            }else{
                if($info['type'] != 1){
                    return $this->output_error(10016,'不是管理员');
                }
            }
        }else{
            $info = Db::name('user')->where(['mobile'=>$mobile,'type'=>1])->find();
            if($info == null){
                return $this->output_error(10016,'不是管理员');
            }
        }

        return $this->output_success(100112,'','验证成功');

    }

    public function statistics(){
        $Member = Db::name('user')->where(['type'=>2])->count();
        $Amount = Db::name('volume')->value('total_money');
        $Consumption = Db::name('consumption')->sum('spend');
        $Num = Db::name('consumption')->sum('num');

        return $this->output_success(100112,['user_member'=>$Member,'amount'=>$Amount,'consumption'=>$Consumption,'num'=>$Num],'成功');

    }

    public function getAccessToken(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::$appid.'&secret='.self::$appsecret;
        $curl = new Curl();
        $res = $curl->get($url);
        $info =  json_decode($res,true);
        return $info['access_token'];

    }

    public function  qrcode(){
        $redis = new Redis();
        $qrcode_img = $redis->get('qr_img');
        if(!$qrcode_img){
            $access_token = $this->getAccessToken();
            $curl = new Curl();
            $url = self::$qr_url.$access_token;
            $data = ['path'=>'pages/admin_index/admin_index','width'=>'50'];
            $res =  $curl->post($url,json_encode($data));

            if($res){
                $date = date('Y-m-d',time());
                $time = time().'.jpg';
                $filepath = '/www/qecup/public/upload/'.$date.'/'.$time;
                $filename = '/www/qecup/public/upload/'.$date;

                if(!file_exists($filename)){
                    mkdir($filename,0777,true);
                }

                $fileUrl = config('server_address').'/upload/'.$date.'/'.$time;
                file_put_contents($filepath,$res);
                $redis->set('qr_img',$fileUrl);
                return $this->output_success(10050,['qrcode_img'=>$fileUrl],'获取二维码成功 ');
            }else{
                return $this->output_error(10111,'未获取到二维码');
            }

        }else{
            return $this->output_success(10050,['qrcode_img'=>$qrcode_img],'获取二维码成功 ');
        }




    }

    //二进制转图片image/png
    public function data_uri($contents, $mime)
    {
        $base64   = base64_encode($contents);
        return ('data:' . $mime . ';base64,' . $base64);
    }


}
