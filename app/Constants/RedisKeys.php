<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020 https://github.com/hyper-im All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-14 下午2:05
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Constants;


class RedisKeys
{
    /**
     * @Message("私人聊天")
     */
    const ROOM_ONLINE = 'room_online_user';

    /**
     * @Message("频道聊天")
     */
    const USER_ON_ROOM = 'user_on_room';

    /**
     * @Message("绑定关系,uid为键值")
     */
    const USER_BIND_FD = 'user_bind_fd';

    /**
     * @Message("绑定关系,fd为键值")
     */
    const FD_BIND_USER = 'fd_bind_user';
}
