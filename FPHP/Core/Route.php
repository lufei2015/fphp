<?php
/**
 *
 * User: 30feifei@gamil.com
 * Date: 2016/6/22
 * Time: 17:48
 */

namespace FPHP\Core;

use FPHP\Core\Config,
    FPHP\Core\Factory;
use FPHP\FPHP;
use FPHP\Protocol\Request;

class Route
{

    public static function route()
    {

        list($action, $method) = Request::getMethod();
        $action = '\\' . Config::get('app_path', 'apps') . "\\Controller\\" . $action;
        $class = Factory::getInstance($action);
        try {
            if (!method_exists($class, $method)) {
                throw new \Exception("method error {$action}::{$method}");
            }
            $class->$method();
        } catch (\Exception $e) {
            FPHP::exceptionHandler($e);
        }
    }

}