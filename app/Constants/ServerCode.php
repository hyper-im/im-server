<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020 https://github.com/hyper-im All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-14 上午9:35
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Constants;


use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * Notes: 文件类注释说明
 * Class ServerCode
 * @package App\Constants
 * @Constants()
 */
class ServerCode extends AbstractConstants
{
    /**
     * @Message("私人聊天")
     */
    const CHAT_PRIVATE = 'chat_private';

    /**
     * @Message("频道聊天")
     */
    const CHAT_CHANNEL = 'chat_channel';

    /**
     * @Message("广播消息")
     */
    const CHAT_BROADCAST = 'chat_broadcast';

    /**
     * @Message("登录")
     */
    const LOGIN = 'login';

    /**
     * @Message("退出")
     */
    const LOGOUT = 'logout';

    const PARAMS = ['chat_private', 'chat_channel', 'chat_broadcast', 'login', 'logout'];
}
