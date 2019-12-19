<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Resources\BannerResource;
use App\Model\Banner;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    public function banner(){
        try{

            //if(!cache()->store("memcached")->has("banner")){
                //echo 1;
                $banner=Banner::all();
                //cache()->store("memcached")->set("banner",$banner,30);
           // }else {
                //echo 2;
               // $banner=cache()->store("memcached")->get("banner");
           // }
            
            if($banner){
                return ["status"=>"20000","message"=>"ok","data"=>$banner];
            }else{
                return ["status"=>"20000","message"=>"没有数据"];
            }

        }catch(ApiException $e){
            return response(["status"=>"500","message"=>$e->getMessage()],500);
        }

    }
}
