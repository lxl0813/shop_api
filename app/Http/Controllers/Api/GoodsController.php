<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GoodResource;
use App\Model\Album;
use App\Model\Attr;
use App\Model\Cate;
use App\Model\Good;
use App\Model\GoodsAttr;
use App\Model\Product;
use App\Model\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;

class GoodsController extends Controller
{
    public function goods(Request $request)
    {
        //echo 3;
        $cate_id = $request->input("cate_id");
        //echo 2;
        if (!Redis::get("cate_" . $cate_id . "_goods")) {
            //echo 1;
            $goods = Cate::find($cate_id)->good()->get();
            $good = GoodResource::collection($goods)->toArray($goods);
            Redis::set("cate_" . $cate_id . "_goods", json_encode($good, true));
        } else {
            //echo 2;
            $good = json_decode(Redis::get("cate_" . $cate_id . "_goods"), true);
        }
        if ($good) {
            return response(["status" => 20000, "message" => "ok", "data" => $good]);
        } else {
            return response(["status" => 40006, "message" => "对不起，没有查询到相关商品"]);
        }
    }


    //获取商品详情
    public function getgoods(Request $request)
    {
        $goods_id = $request->input("goods_id");
        //var_dump($goods_id);exit;
        $goods = Good::find($goods_id);
        $goods_price=$goods["goods_price"];
        //获取商品的类型分组
        $type = Type::find($goods->type_id);
        $group = explode("|", $type->type_group);


        //获取商品的详细参数
        $attr_id= DB::table("goods_attr")->select("attr_id","attr_value")->where(["goods_id" => $goods_id])->get();
        $attr=[];
        foreach ($attr_id as $k => $v) {
            $c=DB::table("attr")->where(["attr_id" => $v->attr_id,"attr_type"=>1])->select("attr_name","attr_id")->get()->toArray() ;
            if($c){
                $attr[$c[0]->attr_name]=$v->attr_value;
            }
        }

        //获取商品的规格
        $attr_id= DB::table("goods_attr")->select("goods_attr_id","attr_id","attr_value")->where(["goods_id" => $goods_id])->get();
        $goods_attr=[];
        foreach ($attr_id as $k => $v) {
            $c=DB::table("attr")->where(["attr_id" => $v->attr_id,"attr_type"=>0])->select("attr_id","attr_name")->get()->toArray() ;
            if($c){
                $v->id=$v->goods_attr_id;
                $v->name=$v->attr_value;
                $v->imgUrl=$goods["goods_img"];
                $v->previewImgUrl=$goods["goods_img"];
                $goods_attr[$c[0]->attr_name][]=$v;
            }
        }
        $tree=[];
        foreach($goods_attr as $k=>$v){
            $obj=new \stdClass();
            $obj->k=$k;
            $obj->v=$v;
            $obj->k_s="s".$v[0]->attr_id;
            $tree[]=$obj;

        }
        //var_dump($tree);exit;
        $product=Product::where("goods_id",$goods_id)->get()->toArray();
        //var_dump($product);exit;
        $num=0;
        foreach($product as $k=>$v){
            $product[$k]["goods_attrs_id"]=explode("|",trim($v['goods_attrs_id'],"|"));
            $num+=$v["products_num"];
        }
        //var_dump($product);die;
        $list=[];
        foreach($product as $k=>$v){
            $obj=new \stdClass();
            $obj->id=$v["products_id"];
            $obj->price=$v["products_price"];
            $obj->stock_num=$v["products_num"];

            foreach($v["goods_attrs_id"] as $key=>$val){ 
                $attr_id=GoodsAttr::find($val);
                $id="s".$attr_id["attr_id"];
                $obj->$id=$val;
            }
            $list[]=$obj;
        }
        //var_dump($list);exit;
        if ($goods) {
            return response(["status" => 20000, "message" => "ok", "goods" => $goods, "group" => $group,"attr"=>$attr,"tree"=>$tree,"num"=>$num,"list"=>$list,"goods_price"=>$goods_price]);
        } else {
            return response(["status" => 40000, "message" => "no"]);
        }
    }

}
