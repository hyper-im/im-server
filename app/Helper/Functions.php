<?php
/**
 * Custom global functions
 */

function user_func(): string
{
    return 'hello';
}

use App\Utils\Message;

if(!function_exists('success')){
    function success($data){
        return Message::success('success', Message::CODE_SUCCESS, $data);
    }
}

if(!function_exists('error')){
    function error($data){
        return Message::error('error', Message::CODE_ERROR, $data);
    }
}

/**
 * 生成随机数
 */
if (!function_exists('randNum')) {
    function randNum($len = 4)
    {
        $chars = '1234567890';
        $string = '';
        for ($i = 0; $i < $len; $i++) {
            $rand = rand(0,strlen($chars)-1);
            $string .= substr($chars,$rand,1);
        }
        return $string;
    }
}

if(!function_exists('get_onlineip')){
    function get_onlineip() {
        $onlineip = '';
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;
    }
}

if (!function_exists('random_salt')) {
    function random_salt($length=6)
    {
        $arr = array_merge(range(0, 9), range('A', 'Z'));
        $invitecode = '';$arr_len = count($arr);
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $arr_len - 1);
            $invitecode .= $arr[$rand];
        }
        return $invitecode;
    }
}

