<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;

class RefreshTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $refresh_token=$request->input("refresh_token");
        if($refresh_token==""){
            return response(["status"=>50000,"message"=>"refresh_token不存在"],200);
        }
        try{
            JWT::decode($refresh_token,env("JWT_SECRET"),["HS256"]);
            //$jwt=(array)$decoded;
            //print_r($jwt);
            return $next($request);
        }catch(\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
            return response(["status"=>60000,"message"=>"refresh_token不正确"],200);
        }catch(\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            return response(["status"=>60000,"message"=>"refresh_token不正确"],200);
        }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            return response(["status"=>60000,"message"=>"refresh_token不正确"],200);
        }catch(\Exception $e) {  //其他错误
            return response(["status"=>60000,"message"=>"refresh_token不正确"],200);
        };

        return $next($request);
    }
}
