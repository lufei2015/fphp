<?php
/**
 * 
 * User: 30feifei@gmail.com
 * Date: 2016/6/22
 * Time: 11:31
 */
use FPHP\FPHP;
$rootPath = dirname(__DIR__);
include ($rootPath.'/FPHP/FPHP.php');
$configPath = $rootPath.'/apps/config';
FPHP::run($rootPath,$configPath);
