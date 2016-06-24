<?php
/**
 *
 * User: 30feifei@gamil.com
 * Date: 2016/6/23
 * Time: 15:53
 */

namespace FPHP\Db;


use FPHP\Logger\FileLogger;

class Redis
{
    /**
     * redis配置信息
     *
     * @var array
     */
    private static $_redisConfig = array();

    /**
     * redis实例列表
     *
     * @var array
     */
    private static $_instances = array();

    private $debug = true;

    /**
     * redis 访问句柄
     *
     * @var Redis|string
     */
    private $_redisHandle = '';

    private function __construct($instName)
    {
        self::$_mongoConfig = Config::get('RedisDb');
        if (!isset(self::$_redisConfig[$instName]))
            throw new Exception("not found redis instance: {$instName}");

        try {
            $this->_redisHandle = new \Redis();
            $this->_redisHandle->connect(self::$_redisConfig[$instName]['server'], self::$_redisConfig[$instName]['port']);
            $this->_redisHandle->select(self::$_redisConfig[$instName]['db']);
        } catch (RedisException $e) {
            throw new Exception($e->getMessage() . '|code:' . $e->getCode());
        }


    }

    /**
     * 获取redis实例
     *
     * @param string $instName 实例名称
     * @return mixed
     */
    public static function getInstance($instName = 'default')
    {
        if (!isset(self::$_instances[$instName])) {
            self::$_instances[$instName] = new self($instName);
        }

        return self::$_instances[$instName];
    }

    /**
     * 调用redis方法
     *
     * @param string $method    方法名
     * @param string $arguments 参数
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $ret = call_user_func_array(array($this->_redisHandle, $method), $arguments);
        if (DEV === 'dev') {
            FileLogger::debug('call Redis ::' . $method . '->' . var_export($arguments, true));
        }
        return $ret;
    }
}