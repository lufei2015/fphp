<?php
/**
 * 
 * User: 30feifei@gamil.com
 * Date: 2016/6/23
 * Time: 14:45
 */

namespace apps\Controller;


use FPHP\IController;
//use FPHP\Protocol\Response;

class Index extends IController {

    public function index(){
        throw new \Exception("no class ");
        \FPHP\Protocol\Response::success(array(__FILE__,__CLASS__,__FUNCTION__));
    }
}