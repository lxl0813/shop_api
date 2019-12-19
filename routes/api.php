<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//注册登录
Route::post("login","Api\AuthController@login");
Route::post("register","Api\AuthController@register");
Route::post("refreshToken","Api\AuthController@refreshToken")->middleware('RefreshTokenAuth');
//验证码发送
Route::post("sendMsg","Api\SendMsgController@sendMsg");
//取轮播图
Route::get("banner","Api\BannerController@banner");
//分类
Route::get("cate","Api\CateController@cate");
//取子分类
Route::post("soncate","Api\CateController@soncate");
//取商品
Route::post("goods","Api\GoodsController@goods");
//取商品详情
Route::post("getgoods","Api\GoodsController@getgoods");
//取商品参数
Route::post("getcan","Api\GoodsController@getcan");
//登陆后添加购物车
Route::post("addCartToMongo","Api\CartController@addCartToMongo")->middleware('addCartToken');
//点击修改购物车商品数量
Route::post("updateNum","Api\CartController@updateNum")->middleware('addCartToken');


Route::middleware(['TokenAuth'])->group(function (){
    //展示购物车
    Route::post("cart","Api\CartController@cart");
    //提交购物车信息，查询最新信息
    Route::post("order","Api\OrderController@order");
    //提交订单进行审核
    Route::post("examineOrder","Api\OrderController@examineOrder");
});