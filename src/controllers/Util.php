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

    public static function textutf($text)
    {
        return utf8_decode($text);
    }

    public static function mask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }
}