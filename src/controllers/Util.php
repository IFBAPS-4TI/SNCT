<?php


use Firebase\JWT\JWT;

class Util
{
    public static function getToken()
    {
        return $_SERVER["jwt_key"];
    }
    public static function decodeToken($token){
        return JWT::decode($token, Util::getToken(), array('HS256'));
    }

    public static function encodeToken($array){
        return JWT::encode($array, Util::getToken());
    }
}