<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020 https://github.com/hyper-im All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-14 下午1:50
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Ws;


use App\Constants\RedisKeys;
use Hyperf\Di\Annotation\Inject;

class UserCollect
{
    /**
     * @Inject()
     * @var \Redis
     */
    protected $redis;
    public static $userInfo;
    public static $userInfoFd;

    public function joinIm($fd, $data){

        $channel = $data['channel']??0;
        $uid = $data['uid']??0;

        if($channel){
            $channelKey = RedisKeys::ROOM_ONLINE.$channel;
            if(!$this->redis->hExists($channelKey, $fd)){
                $this->redis->hSet($channelKey, $fd, $uid);
            }
        }

        if($uid){
            $user_on_room_key = RedisKeys::USER_ON_ROOM;
            if(!$this->redis->hExists($user_on_room_key, $uid)){
                $this->redis->hSet($user_on_room_key, $uid, $channel);
            }
        }

        if($fd){
            $this->redis->hSet(RedisKeys::USER_BIND_FD, $fd, $uid);
        }

        if($uid){
            $this->redis->hSet(RedisKeys::FD_BIND_USER, $uid, $fd);
        }
    }

    public function outIm($fd){
        $fd = (string)$fd;
        /*
         * get im user , if im user exist , keep running
         */
        $uid = (string)$this->redis->hGet(RedisKeys::USER_BIND_FD, $fd);
        if (!is_bool($uid)) {
            $roomId = $this->redis->hGet(RedisKeys::USER_ON_ROOM, $uid);
            $this->redis->hDel(RedisKeys::ROOM_ONLINE.$roomId, $uid);
            $this->redis->hDel(RedisKeys::USER_ON_ROOM, $uid);
            $this->redis->hDel(RedisKeys::USER_BIND_FD, $fd);
        }
    }

    public static function addUser($uid, $user){
        if(!array_key_exists($uid, self::$userInfo)){
            self::$userInfo[$uid] = $user;
        }
    }

    public static function addUserByFd($fd, $user){
        if(!array_key_exists($fd, self::$userInfoFd)){
            self::$userInfoFd[$fd] = $user;
        }
    }

    public static function getUser($uid){
        if(!array_key_exists($uid, self::$userInfo)){
            return self::$userInfo[$uid];
        }
        return [];
    }

    public static function delUser($uid){
        if(array_key_exists($uid, self::$userInfo)){
            unset(self::$userInfo[$uid]);
        }
    }

    public static function list(){
        return self::$userInfo;
    }

    public function getFdByChannel($channel){
        $result = [];
        if($channel){
            $channelKey = RedisKeys::ROOM_ONLINE.$channel;
            $result =  $this->redis->hGetAll($channelKey);
        }
        return $result;
    }

    public function getFdByUid($uid){
        $fd = false;
        if($uid){
            return $this->redis->hGet(RedisKeys::FD_BIND_USER, $uid);
        }
        return $fd;
    }

    public static function getUserByFd($fd){
        if(!array_key_exists($fd, self::$userInfoFd)){
            return self::$userInfoFd[$fd];
        }
    }

    public static function getUidByFd($fd){
        if(!array_key_exists($fd, self::$userInfoFd)){
            return self::$userInfoFd[$fd]['uid'];
        }
    }
}
