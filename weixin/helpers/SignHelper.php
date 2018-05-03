<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2018/1/22
 * Time: 下午9:59
 */

namespace yuanshuai\yscomponents\weixin\helpers;


class SignHelper
{
    public static function Check($token, $signature, $timestamp, $nonce)
    {
        $params = [$token,$timestamp,$nonce];
        sort($params,SORT_STRING);
        $str = implode($params);
        $theSign = sha1($str);
        return $theSign == $signature;
    }

    public static function CheckSignature($data,$signature,$wechatKey)
    {
        unset($data["sign"]);
        ksort($data,SORT_STRING);
        $str = "";
        foreach ($data as $key => $value) {
            $str .= "{$key}=$value&";
        }
        $str .= "key=".$wechatKey;
        return hash_hmac("sha256",$str,$wechatKey) == $signature;
    }
}