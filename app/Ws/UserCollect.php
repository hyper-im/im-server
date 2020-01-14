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

    public function joinIm($fd, object $joinInfo){
        /*
         * If im user joined room before , just take im user out the room
         */
//        $this->outRoom($this->frame->fd);
        /*
         * take im user in room redis
         */
        $this->redis->hSet(RedisKeys::ROOM_ONLINE.$joinInfo->room_id, $joinInfo->uid, $fd);
        /*
         * bind im user to room id
         */
        $this->redis->hSet(RedisKeys::USER_ON_ROOM, $joinInfo->uid, $joinInfo->room_id);
        /*
         * bind websocket fd to im user
         */
        $this->redis->hSet(RedisKeys::USER_BIND_FD, $fd, $joinInfo->uid);
        $this->redis->hSet(RedisKeys::FD_BIND_USER, $joinInfo->im_id, $fd);
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

    public static function delUser($uid){
        if(array_key_exists($uid, self::$userInfo)){
            unset(self::$userInfo[$uid]);
        }
    }

    public static function list(){
        return self::$userInfo;
    }
}
