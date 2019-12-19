<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CateResource;
use App\Model\Cate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;


class CateController extends Controller
{
    public function cate(){
        if(!Redis::get("cate_0")){
            //echo 11;
            $cates=Cate::where("cates_pid",0)->get();
            $cate=CateResource::collection($cates)->toArray($cates);
            Redis::set("cate_0",json_encode($cate,true));
        }else{
            //echo 22;
            $cate=json_decode(Redis::get("cate_0"),true);
        }
        if($cate){
            return response(["status"=>20000,"message"=>"ok","data"=>$cate]);
        }else{
            return response(["status"=>40006,"message"=>"no"]);
        }

    }

    public function soncate(Request $request){
        $cate_id=$request->input("cate_id");
        //var_dump($cate_id);
        if(!Redis::get("cate_".$cate_id)){
            $cates=Cate::where("cates_pid",$cate_id)->get();
            $cate=CateResource::collection($cates)->toArray($cates);
            Redis::set("cate_".$cate_id,json_encode($cate,true));
        }else{
            $cate=json_decode(Redis::get("cate_".$cate_id),true);
        }
        if($cate){
            return response(["status"=>20000,"message"=>"ok","data"=>$cate]);
        }else{
            return response(["status"=>40006,"message"=>"对不起，没有查询到相关分类"]);
        }

    }
}