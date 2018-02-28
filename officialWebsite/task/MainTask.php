<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-02-28
 * Time: 下午 14:09
 */
use Phalcon\Cli\Task;

class MainTask extends Task
{
    public function mainAction()
    {
        echo 'This is the default task and the default action' . PHP_EOL;
    }

}