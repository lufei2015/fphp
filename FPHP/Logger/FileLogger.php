<?php
/**
 * 
 * User: 30feifei@gamil.com
 * Date: 2016/6/23
 * Time: 16:03
 */

namespace FPHP\Logger;


class FileLogger {
    private static $log_items = array();
    private static $log_level = LOG_INFO;
    private static $log_path = '/tmp/';

    /**
     *
     * @param string $uid
     */
    public static function set_path($path)
    {
        self::$log_path = $path;
    }

    /**
     *
     * @param int $level
     */
    public static function set_level($level = LOG_INFO)
    {
        self::$log_level = $level;
    }

    /**
     *
     * @param int    $log_level
     * @param string $message
     */
    public static function log($log_level, $message)
    {
        $log_level_names = array(
            LOG_INFO    => 'INFO',
            LOG_ERR     => 'ERROR',
            LOG_WARNING => 'WARNING',
            LOG_DEBUG   => 'DEBUG',
            LOG_NOTICE  => 'NOTICE',
        );
        self::$log_items[] = sprintf("%s|%-015s|%'~7s|%s",
            date('Y-m-d H:i:s'),
            microtime(1),
            $log_level_names[$log_level],
            $message
        //json_encode(debug_backtrace())
        );

    }

    /**
     *
     * @param  string $message 消息
     * @return mixed
     */
    public static function info($message)
    {
        self::log(LOG_INFO, $message);
    }

    /**
     *
     * @param string $message 消息
     * @param string $category
     * @return mixed
     */
    public static function error($message)
    {
        self::log(LOG_ERR, $message);
    }

    /**
     *
     * @param string $message 消息
     * @param string $category
     * @return mixed
     */
    public static function warning($message)
    {
        self::log(LOG_WARNING, $message);
    }

    /**
     *
     * @param string $message 消息
     * @param string $category
     * @return mixed
     */
    public static function notice($message)
    {
        self::log(LOG_NOTICE, $message);
    }

    /**
     *
     * @param string $message 消息
     * @param string $category
     * @return mixed
     */
    public static function debug($message)
    {
        self::log(LOG_DEBUG, $message);
    }

    /**
     *
     * @return array
     */
    public static function get_all_logs()
    {
        return self::$log_items;
    }

    /**
     *
     * @return mixed
     */
    public static function clear()
    {
        if (self::$log_items) {
            foreach (self::$log_items as $log_items) {
                $logName = self::$log_path.MODULE.'_'.date("Ymd") . '.log';
                error_log($log_items . PHP_EOL, 3, $logName);
            }
        }

        self::$log_items = array();
        self::$log_level = LOG_INFO;
    }
}