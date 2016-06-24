<?php
/**
 * 命令行
 * User: 30feifei@gamil.com
 * Date: 2016/6/22
 * Time: 18:29
 */

namespace FPHP\Adapter;


class Cli {
    public function run(){
        $_SERVER['argv']['r']=$_SERVER['argv'][1];
        Request::setParams($_SERVER['argv']);
        Route::route();
    }
}