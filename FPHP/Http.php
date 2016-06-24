<?php
/**
 * 
 * User: 30feifei@gamil.com
 * Date: 2016/6/22
 * Time: 18:27
 */

namespace FPHP\Adapter;

use FPHP\Core\Route;
use FPHP\Protocol\Request;

class Http {
    public function run(){
        Request::setParams($_REQUEST);
        Route::route();
    }
}