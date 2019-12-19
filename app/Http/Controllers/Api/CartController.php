<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    //用户登录后添加到购物车的
    public function addCartToMongo(Request $request){
        $cart=$request->input();
        //var_dump($cart);exit;
        $user_id=$request->user_id;
        //var_dump($user_id);exit;
        $cart["user_id"]=$user_id;
        $carts=DB::connection('mongodb')
                ->collection('cart')
                ->where(["user_id"=>$user_id,"goods_id"=>$cart["goods_id"],"product_id"=>$cart["product_id"]])
                ->first();
        if($carts){
            echo 1;
            $num=$carts["cart_number"]+$cart["cart_number"];
            DB::connection('mongodb')
                ->collection('cart')
                ->where(["user_id"=>$user_id,"goods_id"=>$cart["goods_id"],"product_id"=>$cart["product_id"]])
                ->update(["cart_number"=>$num]);
        }else{
            echo 2;
            DB::connection('mongodb')
                ->collection('cart')
                ->insert($cart);
        }
        return response(["status"=>20000,"message"=>"添加购物车成功"]);
    }


    //获取购物车信息
    public function cart(Request $request){
        $user_id=$request->user_id;
        $carts=DB::connection('mongodb')
            ->collection('cart')
            ->where(["user_id"=>$user_id])
            ->get();
        //var_dump($carts);exit;
        if($carts){
            return response(["status"=>20000,"message"=>"购物车加载成功","data"=>$carts],200);
        }else{
            return response(["status"=>40000,"message"=>"您还没有添加购物车哦！赶快取添加吧！"]);
        }
    }

    //点击修改购物车商品数量
    public function updateNum(Request $request){
        echo 12;exit;
        $user_id=$request->user_id;
        $shopCart=$request->input();
    }
}
