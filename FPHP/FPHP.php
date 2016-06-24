<?php
/**
 *
 * User: 30feifei@gmail.com
 * Date: 2016/6/22
 * Time: 11:14
 */
namespace FPHP;

use FPHP\Core\Config;
use FPHP\Adapter\Cli;
use FPHP\Adapter\Http;
use FPHP\Logger\FileLogger;
use FPHP\Protocol\Response;

class FPHP
{

    private static $configPath = 'default';
    private static $appPath = 'apps';
    private static $fPath;
    private static $libPath = '';
    private static $classPath = array();

    public static function getAppPath()
    {
        return self::$appPath;
    }

    public static function autoLoader($class)
    {
        if (isset(self::$classPath[$class])) {
            require self::$classPath[$class];
            return;
        }
        $baseClasspath = str_replace('\\', DS, $class) . '.php';
        $libs = array(
            self::$appPath,
            self::$fPath
        );

        if (is_array(self::$libPath)) {
            $libs = array_merge($libs, self::$libPath);
        } else {
            $libs[] = self::$libPath;
        }
        foreach ($libs as $lib) {
            $classpath = $lib . DS . $baseClasspath;
            if (is_file($classpath)) {
                self::$classPath[$class] = $classpath;
                require "{$classpath}";
                return;
            }
        }

    }

    final public static function exceptionHandler($exception)
    {
        FileLogger::error($exception->getCode().'::::'.$exception->getMessage().' - file:'.$exception->getFile().' line '.$exception->getLine());
        Response::fail($exception->getCode(),$exception->getMessage());
    }

    final public static function errorHandler($err_no, $err_msg, $err_file, $err_line)
    {
        if (!(error_reporting() & $err_no)) {
            return;
        }
        FileLogger::error("[" . $err_no . "]" . $err_msg . " in " . $err_file . " at " . $err_line . " line");
    }

    final public static function fatalHandler()
    {
        FileLogger::clear();
        $error = \error_get_last();
        if (empty($error)) {
            return;
        }
        if (!in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            return;
        }
        FileLogger::error("[" . $error['type'] . "]" . $error['message'] . " in " . $error['file'] . " at " . $error['line'] . " line");
        FileLogger::clear();
    }


    public static function run($rootPath, $configPath)
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        self::runDomain();
        self::$fPath = dirname(__DIR__);
        self::$configPath = $configPath . DS . DEV;
        spl_autoload_register(__CLASS__ . '::autoLoader');
        //域名
        self::runDomain();
        //加载配置
        Config::load(self::$configPath);
        self::$appPath = $rootPath . DS . Config::get('app_path', 'apps');
        spl_autoload_register(__CLASS__ . '::autoLoader');
        set_exception_handler(__CLASS__ . '::exceptionHandler');
        register_shutdown_function(array(__CLASS__, 'fatalHandler'));
        set_error_handler(array(__CLASS__, 'errorHandler'));
        $timeZone = Config::get('time_zone', 'Asia/Shanghai');
        date_default_timezone_set($timeZone);

        if (MODULE == 'CLI') {
            $service = new Cli();
            $service->run();
        } else {
            $service = new Http();
            $service->run();
        }
    }

    /**
     * $_SERVER['HTTP_HOST'] = http://android-1-dev.a.com
     * @param $configPath
     * @return array
     */
    public static function runDomain()
    {

        if (PHP_SAPI === 'cli') {
            if (!defined('DEV')) define('DEV', 'dev');
            if (!defined('MODULE')) define('MODULE', 'CLI');
        } else {
//            $arr = explode('-', $_SERVER['HTTP_HOST']);
//            $palefrom = empty($arr[0]) ? 'android' : $arr[0];
//            $serverid = empty($arr[1]) ? 0 : $arr[1];
//            $dev = empty($arr[2]) ? 'dev' : substr($arr[2], 0, strpos($arr[2], '.'));
//            unset($arr);
//            define('SERVERID', $serverid);
//            define('PALEFROM', $palefrom);
//            define('DEV', $dev);
            if (!defined('DEV')) define('DEV', 'dev');
            if (!defined('MODULE')) define('MODULE', 'HTTP');
        }
    }
}
