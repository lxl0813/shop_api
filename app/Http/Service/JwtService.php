<?php
namespace App\Http\Service;
use Firebase\JWT\JWT;

class JwtService
{
    public $iss = 'http://www.api.com';
    public $aud = 'http://www.api.com';

    public static function getToken($data, $exp = 7200)
    {
        $nowTime = time();
        $token = [
            'iss' => 'http://www.api.com',//签发者
            'aud' => 'http://www.api.com', //jwt所面向的用户
            'iat' => $nowTime, //签发时间
            'nbf' => $nowTime, //在什么时间之后该jwt才可用
            'exp' => $nowTime + $exp, //过期时间-10min
            'data' => $data
        ];
        $jwt = JWT::encode($token, env("JWT_SECRET"));
        return $jwt;
    }


}