<?php


use Firebase\JWT\JWT;

class Util
{
    public static function getToken()
    {
        return $_SERVER["jwt_key"];
    }

    public static function decodeToken($token)
    {
        return JWT::decode($token, Util::getToken(), array('HS256'));
    }

    public static function encodeToken($array)
    {
        return JWT::encode($array, Util::getToken());
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}