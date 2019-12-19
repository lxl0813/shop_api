<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;


class addCartToken
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
        $token=$request->header("token");
        if($token==""){
            return response(["status"=>50000,"message"=>"token不存在"],200);
        }
        try{
            $decoded=JWT::decode($token,env("JWT_SECRET"),["HS256"]);
            $jwt=(array)$decoded;
            //var_dump($jwt);exit;

        }catch(\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
            return response(["status"=>50000,"message"=>"token不正确"],200);
        }catch(\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            return response(["status"=>50000,"message"=>"token不正确"],200);
        }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            return response(["status"=>50001,"message"=>"token过期"],200);
        }catch(\Exception $e) {  //其他错误
            return response(["status"=>50000,"message"=>"其他错误"],200);
        }
        $request->user_id=$jwt['data']->user_id;
        return $next($request);
    }
}
