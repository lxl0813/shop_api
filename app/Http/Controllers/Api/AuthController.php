<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Login;
use App\Http\Requests\Register;
use App\Http\Service\JwtService;
use App\Model\Mongodb;
use App\Model\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    public function register(Register $request){
        $user_phone=$request->input("user_phone");
        $code=$request->input("code");
        $user_pwd=$request->input("user_pwd");
//        if($code!=Cache::get("code")){
//            return response(["status"=>40000,"message"=>"验证码错误"]);
//        }
        $reg_phone='/^1[3456789]\d{9}$/';
        $reg_email='/^\w+@[a-z0-9]+\.[a-z]{2,4}$/';
        if(!preg_match($reg_phone,$user_phone) && !preg_match($reg_email,$user_phone)){
            return response(["status"=>40000,"message"=>"输入的账号不符合要求"],200);
        }

        //根据用户输入的账号类型进行相应的入库
        if(preg_match($reg_phone,$user_phone)){
            $phone=User::where("user_phone",$user_phone)->first();
            if($phone){
                return response(["status"=>40000,"message"=>"该账号已注册"],200);
            }else{
                $user_pwd=encrypt($user_pwd);
                $user_name="ZJ—".substr(uniqid(),-8);
                $userModel = new User();
                $userModel->user_name=$user_name;
                $userModel->user_phone=$user_phone;
                $userModel->user_pwd=$user_pwd;
                $arr=$userModel->save();
                if($arr){
                    //注册成功需要分配一个token
                    $data=["user_id"=>$userModel->user_id];
                    $jwt = new JwtService();
                    $token=$jwt -> getToken($data);
                    $refresh_token=$jwt->getToken($data,24*3600*30);
                    return response(["status"=>20000,"message"=>"注册成功","token"=>$token,"refresh_token"=>$refresh_token],200);
                }else{
                    return response(["status"=>40001,"message"=>"注册失败"],200);
                }
            }
        }

        if(preg_match($reg_email,$user_phone)){
            $phone=User::where("user_email",$user_phone)->first();
            if($phone){
                return response(["status"=>40000,"message"=>"该账号已注册"],200);
            }else{
                $user_pwd=encrypt($user_pwd);
                $user_name="ZJ—".substr(uniqid(),-8);
                $userModel = new User();
                $userModel->user_name=$user_name;
                $userModel->user_email=$user_phone;
                $userModel->user_pwd=$user_pwd;
                $arr=$userModel->save();
                if($arr){
                    //注册成功需要分配一个token
                    $jwt = new JwtService();
                    $token=$jwt -> getToken(["user_id"=>$userModel->user_id]);
                    $refresh_token=$jwt->getToken(["user_id"=>$userModel->user_id],24*3600*30);
                    //dd($refresh_token);
                    return response(["status"=>20000,"message"=>"注册成功","token"=>$token,"refresh_token"=>$refresh_token],200);
                }else{
                    return response(["status"=>40001,"message"=>"注册失败"],200);
                }
            }
        }

    }

    //登录
    public function login(Login $request){
        $name=$request->input("user_phone");
        $pwd=$request->input("user_pwd");
        $cart=$request->input("cart");
        //var_dump($cart);exit;
        $reg_phone='/^1[3456789]\d{9}$/';
        $reg_email='/^\w+@[a-z0-9]+\.[a-z]{2,4}$/';
        if(!preg_match($reg_phone,$name) && !preg_match($reg_email,$name)){
            return response(["status"=>40000,"message"=>"输入的账号不符合要求"],200);
        }

        if(preg_match($reg_phone,$name)) {
            $user = new User();
            $data = $user->where('user_phone', $name)->first();
            //var_dump($data["user_id"]);exit;
            if ($data) {
                if ($pwd === $pwd) {
                    $id = ['user_id' => $data["user_id"]];
                    //var_dump($id);exit;
                    $user_id=$data['user_id'];
                    $carts=[];
                    foreach($cart as $key=>$val){
                        $val["user_id"]=$user_id;
                        $carts[]=$val;
                    }

                    foreach($carts as $k=>$v){
                        //$carts=$v;
                        $cart=DB::connection('mongodb')
                            ->collection('cart')
                            ->where(["user_id"=>$user_id,"goods_id"=>$v["goods_id"],"product_id"=>$v["product_id"]])
                            ->first();
                        if($cart){
                            //echo 1;
                            $num=$cart["cart_number"]+$v["cart_number"];
                            DB::connection('mongodb')
                                ->collection('cart')
                                ->where(["user_id"=>$user_id,"goods_id"=>$v["goods_id"],"product_id"=>$v["product_id"]])
                                ->update(["cart_number"=>$num]);

                        }else{
                            DB::connection('mongodb')
                                ->collection('cart')
                                ->insert($v);
                        }
                    }
                    $token = JwtService::getToken($id);
                    $refresh_token = JwtService::getToken($id, 30 * 24 * 3600);
                    return response(['status' => 20000, 'msg' => 'ok', 'token' => $token, 'refresh_token' => $refresh_token], 200);
                } else {
                    return response(['status' => 40000, 'msg' => '密码错误'], 200);
                }
            } else {
                return response(['status' => 40000, 'msg' => '用户名错误'], 200);
            }
        }

        if(preg_match($reg_email,$name)) {
            $user = new User();
            $data = $user->where('user_email',$name)->first();
            if ($data) {
                if ($pwd === $pwd) {
                    $id = ['user_id' => $data['users_id']];
                    $token = JwtService::getToken($id);
                    $refresh_token = JwtService::getToken($id, 30 * 24 * 3600);
                    return response(['status' => 20000, 'msg' => 'ok', 'token' => $token, 'refresh_token' => $refresh_token], 200);
                } else {
                    return response(['status' => 40000, 'msg' => '密码错误'], 200);
                }
            } else {
                return response(['status' => 40000, 'msg' => '用户名错误'], 200);
            }
        }
    }

    //刷新token的方法
    public function refreshToken(){
        //验证refesh_token的合法性
        try{
            $refresh_token=request()->input("refresh_token");
            $decoded=JWT::decode($refresh_token,env("JWT_SECRET"),["HS256"]);
            $jwt=(array)$decoded;
            $user_id=$jwt["data"]->user_id;
            //生成新的token和新的refresh_token
            $jwt=new JwtService();
            $token=$jwt -> getToken(["user_id"=>$user_id]);
            $refresh_token=$jwt -> getToken(["user_id"=>$user_id],24*3600*30);
            return response(["status"=>20000,"message"=>"token刷新成功","token"=>$token,"refresh_token"=>$refresh_token]);
        }catch(ApiException $e){
            return response(["status"=>100000,"message"=>"网络异常"],500);
        }


    }
}
