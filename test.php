<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2018 https://www.hyperpay.me All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-9 上午11:28
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

$run = new Coroutine\Run();
$run->add(function () {
    Co::sleep(1);
    echo "Done.\n";
});
$run->start();
