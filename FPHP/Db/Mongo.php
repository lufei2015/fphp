<?php
/**
 *
 * User: 30feifei@gamil.com
 * Date: 2016/6/23
 * Time: 14:55
 */

namespace FPHP\Db;


use FPHP\Core\Config;
use FPHP\Logger\FileLogger;

class Mongo
{
    /**
     * mongo配置信息
     *
     * @var array
     */
    private static $_mongoConfig = array();

    /**
     * mongo实例列表
     *
     * @var array
     */
    private static $_instances = array();

    /**
     * MongoClient访问句柄
     *
     * @var MongoClient|string
     */
    private $_mongoHandle = '';

    /**
     * mongo集合实例
     *
     * @var $_collectionHandle MongoCollection
     */
    private $_collectionHandle = '';

    /**
     * 当前操作的mongo集合
     *
     * @var string
     */
    private $_collection = '';

    /**
     * MongoDB访问句柄
     *
     * @var MongoDB|string
     */
    private $_dbHandle = '';


    //>$gt\<$lt\>=$gte\<=$lte

    /**
     * 构造方法
     *
     * @param string $instName mongo实例名称
     * @throws AppException
     */
    private function __construct($instName)
    {
        self::$_mongoConfig = Config::get('MongoDb');
        if (!isset(self::$_mongoConfig[$instName]))
            throw new Exception("not found mongo instance: {$instName}");

        $config = self::$_mongoConfig[$instName];
        empty($config['dbname']) && $config['dbname'] = 'test';

        if (empty($config['username']) && empty($config['password']))
            $mongoDsn = "mongodb://{$config['server']}:{$config['port']}/{$config['dbname']}";
        else
            $mongoDsn = "mongodb://{$config['username']}:{$config['password']}@{$config['server']}:{$config['port']}/admin";

        try {
            $this->_mongoHandle = new \MongoClient($mongoDsn);
            $this->_dbHandle = $this->_mongoHandle->selectDB($config['dbname']);
        } catch (MongoException $e) {
            throw new Exception($e->getMessage() . '|code:' . $e->getCode());
        }

    }

    /**
     * 获取mongo实例
     *
     * @param string $instName mongo实例名称
     * @return AppMongo
     */
    public static function getInstance($instName = 'default')
    {
        if (!isset(self::$_instances[$instName]))
            self::$_instances[$instName] = new self($instName);

        return self::$_instances[$instName];
    }

    /**
     * 选择要操作的集合
     *
     * @param $collection
     * @return MongoCollection
     */
    public function selectCollection($collection)
    {
        return $this->_collectionHandle = $this->_dbHandle->selectCollection($collection);
    }

    public function close()
    {
        $this->_mongoHandle->close($this->_mongoHandle);
    }

    /**
     * 转发MongoCollection的操作
     *
     * @param string $method    方法
     * @param array  $arguments 参数
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $ret = call_user_func_array(array($this->_collectionHandle, $method), $arguments);
        if (DEV === 'dev') {
            FileLogger::debug('call mongo ' . $this->_collectionHandle . '::' . $method . '->' . var_export($arguments, true));
        }
        return $ret;
    }
}
