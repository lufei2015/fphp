<?php
/**
 *
 * User: 30feifei@gamil.com
 * Date: 2016/6/22
 * Time: 11:39
 *
 */
namespace FPHP\Core;

class Config
{
    private static $configPath;
    private static $config;

    public static function load($configPath, $configName = 'appConfig.php')
    {
        self::$configPath = $configPath;
        self::$config = include self::$configPath . DS . $configName;
        return self::$config;
    }

    public static function get($key, $default = null, $throw = true)
    {
        $result = isset(self::$config[$key]) ? self::$config[$key] : $default;
        if ($throw && is_null($result)) {
            throw new \Exception("{$key} config empty");
        }
        return $result;
    }

    public static function set($key, $value, $set = true)
    {
        if ($set) {
            self::$config[$key] = $value;
        } else {
            if (empty(self::$config[$key])) {
                self::$config[$key] = $value;
            }
        }

        return true;
    }
}