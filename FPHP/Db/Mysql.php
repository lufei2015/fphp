<?php
/**
 *
 * User: 30feifei@gamil.com
 * Date: 2016/6/23
 * Time: 16:19
 */

namespace FPHP\Db;


class Mysql
{
    /**配置信息
     * @var array
     */
    private static $_dbConfig = array();
    /**实例列表
     * @var array
     */
    private static $_instances = array();
    /**当前连接对象
     * @var null
     */
    private $_dbHandle = null;
    //查询结果
    private $PDOStatement = null;

    private $bind = array();

    private $error = false;

    private $dbNmae = '';

    // PDO连接参数
    protected $options = array(
//        PDO::ATTR_CASE               => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    );

    private function __construct($dbName)
    {
        self::$_mongoConfig = Config::get('MysqlDb');
        if (empty(self::$_dbConfig[$dbName]))
            throw new Exception('empty db ' . $dbName);

        try {
            //'mysql:host=localhost;dbname=mydatabase;charset=utf8'
            $this->_dbHandle = new PDO(
                self::$_dbConfig[$dbName]['dns'],
                self::$_dbConfig[$dbName]['user'],
                self::$_dbConfig[$dbName]['password'],
                $this->options);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage() . '|code:' . $e->getCode());
        }
        $this->dbNmae = $dbName;

    }

    /**实例化
     * @param $dbName
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
     * 参数合并
     * @param $bind
     */
    private function parseBind($bind)
    {
        $this->bind = array_merge($this->bind, $bind);
    }

    /**
     * 参数绑定
     * @access protected
     * @param string $name  绑定参数名
     * @param mixed  $value 绑定值
     * @return void
     */
    protected function bindParam($name, $value)
    {
        $this->bind[':' . $name] = $value;
    }

    /**执行SQL并返回
     * @param string $sql
     * @param string $type
     * @return bool
     */
    private function query($sql, $type = '')
    {
        if (empty($sql)) return false;
        if (DEV === 'dev')
            Logger::debug("call mysql {$this->dbNmae} : {$sql}");

        isset($this->PDOStatement) or $this->PDOStatement = null;
        $this->PDOStatement = $this->_dbHandle->prepare($sql);
        if (!empty($this->bind)) {
            foreach ($this->bind as $key => $val) {
                if (is_array($val)) {
                    $this->PDOStatement->bindValue($key, $val[0], $val[1]);
                } else {
                    $this->PDOStatement->bindValue($key, $val);
                }
            }
        }

        $this->bind = array();
        $result = $this->PDOStatement->execute();
        if ($type == 'findOneSql') {
            $row = $this->PDOStatement->fetch(PDO::FETCH_ASSOC);

            return $row;
        } else if ($type == 'findSql') {
            $row = $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);

            return $row;
        } else if ($result !== false) {
            return $this->PDOStatement->rowCount();
        } else if ($result === false) {
            $error = $this->PDOStatement->errorInfo();
            $this->error = $error[1] . ':' . $error[2];
        }
        return false;
    }

    /**
     * 一般执行INSET,DELETE,UPDATE
     * @param       $sql
     * @param array $params
     * @return bool
     */
    public function execute($sql, $params = array())
    {
        if (!empty($params)) {
            $this->bind = array();
            $this->parseBind($params);
        }
        return $this->query($sql, __FUNCTION__);
    }

    /**
     * 查询多条记录
     * @param       $sql
     * @param array $params
     * @return bool
     */
    public function findSql($sql, $params = array())
    {
        if (!empty($params)) {
            $this->bind = array();
            $this->parseBind($params);
        }
        return $this->query($sql, __FUNCTION__);
    }

    /**
     * 查询一条记录
     * @param       $sql
     * @param array $params
     * @return bool
     */
    public function findOneSql($sql, $params = array())
    {
        if (!empty($params)) {
            $this->bind = array();
            $this->parseBind($params);
        }
        return $this->query($sql, __FUNCTION__);
    }

    /**
     * @param       $form
     * @param array $params
     * @param bool  $lastInsertId
     * @return bool
     *
     *
     *
     * $res = $db->insert('user',array('user'=>'userA','password'=>'123'));
     * if($res === false) 'fail'
     * else 'success'
     */
    public function insert($form, $params = array(), $lastInsertId = true)
    {
        if (empty($params)) {
            return false;
        }
        $fields = $values = '';
        foreach ($params as $name => $value) {
            $fields .= '`' . $name . '`,';
            $values .= ":{$name},";
            $this->bindParam($name, $value);
        }
        $fields = rtrim($fields, ',');
        $values = rtrim($values, ',');
        $sql = 'INSERT INTO `' . $form . '` (' . $fields . ') VALUES (' . $values . ')';
        $result = $this->execute($sql);
        unset($fields, $values, $params);
        if ($result === false) {
            return false;
        } else if ($lastInsertId) {
            return $this->lastInsertId();
        } else {
            return $result;
        }
    }

    /**
     * @param       $form
     * @param array $params
     * @param array $where
     * @return bool
     *
     * $db->update('user',array('monty'=>'-1','level'=>'+1','name'=>'FPHP'),array('id'=>1))
     */
    public function update($form, $params = array(), $where = array())
    {
        if (empty($params)) {
            return false;
        }
        $set = '';
        foreach ($params as $name => $value) {
            if (strpos($value, '+')) {
                $value = (int)substr($value, 1);
                $set .= "`{$name}`=`{$name}`+'{$value}',";
            } elseif (strpos($value, '-')) {
                $value = (int)substr($value, 1);
                $set .= "`{$name}`=`{$name}`-'{$value}',";
            } else {
                $set .= "`{$name}`=:{$name},";
                $this->bindParam($name, $value);
            }

        }
        $set = rtrim($set, ',');
        $newWhere = '';
        if (!empty($where)) {
            $newWhere = ' WHERE ';
            foreach ($where as $n => $k) {
                $newWhere .= " `{$n}`='{$k}' AND";
            }
            $newWhere = rtrim($newWhere, 'AND');
        }
        $sql = 'UPDATE `' . $form . '` SET ' . $set . $newWhere;
        $result = $this->execute($sql);
        unset($set, $newWhere, $where, $params);
        if ($result === false) {
            return false;
        } else {
            return $result;
        }
    }

    /**
     * @param       $form
     * @param array $where
     * @return bool
     * $db->Delete('user',array('id'=>1));
     */
    public function Delete($form, $where = array())
    {
        if (empty($where)) return false;
        $newWhere = ' WHERE ';
        foreach ($where as $n => $k) {
            $newWhere .= " `{$n}`='{$k}' AND";
        }
        $newWhere = rtrim($newWhere, 'AND');
        $sql = 'DELETE FROM `' . $form . '`' . $newWhere;
        $result = $this->execute($sql);
        if ($result === false) {
            return false;
        } else {
            return $result;
        }
    }


    /**
     * 最新记录ID
     * @return mixed
     */
    public function lastInsertId()
    {
        return $this->_dbHandle->lastInsertId();
    }

    /**
     * 报错信息
     * @return bool
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 释放查询结果
     * @access public
     */
    public function free()
    {
        $this->PDOStatement = null;
    }

    /**
     * 关闭数据库
     * @access public
     */
    public function close()
    {
        $this->_dbHandle = null;
    }

    /**
     *
     */
    public function __destruct()
    {
        // 释放查询
        if ($this->PDOStatement) {
            $this->free();
        }
        // 关闭连接
        $this->close();
    }
}