<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020 https://github.com/hyper-im All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-14 上午9:34
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Constants;


use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * Notes: 文件类注释说明
 * Class ClientCode
 * @package App\Constants
 * @Constants()
 */
class ClientCode extends AbstractConstants
{
    /**
     * @Message("服务端client注册到router")
     */
    const REGISTER_FROM_SERVER = 'register_from_server';

    /**
     * @Message("用户注册")
     */
    const REGISTER_FROM_USER = 'register_from_user';

    /**
     * @Message("来源server-client")
     */
    const FROM_SERVER_CLIENT = 'from_server_client';

    /**
     * @Message("来源server-client-broadCast广播信息")
     */
    const SERVER_CLIENT_BROADCAST = 'server_client_broadcast';

}
