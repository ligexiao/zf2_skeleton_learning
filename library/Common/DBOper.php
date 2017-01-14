<?php

namespace Common;

use \Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Sql\Insert;

class DBOper
{
    private $config;
    private $db;
    private $inTransaction; //当前连接是否在事务中
    
    const CALLBACK_TYPE_POSTCOMMIT = 'postcommit';
    
    private $callbackQueue = array();
    public function __construct($config)
    {
        $this->config = $config;
        $this->_connect();
    }
    
    private function _connect()
    {
        $this->db = new Adapter($this->config);
    }
    
    /**
     * Query sql
     * @param string $sql
     * @return array
     */
    public function query($sql)
    {
        Log::info($sql);
        return $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    /**
     * Execute sql, return the attected rows count
     * @param string $sql
     * @return int
     */
    public function execute($sql, $lastInsertId=false)
    {
        Log::info($sql);
       	if ($lastInsertId) { 
			return $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE)->getGeneratedValue();
       	} else {
			return $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE)->getAffectedRows();
       	}
    }
    
    public function insert($table, $set, $valueFields = array(), $isStrip = true)
    {
        $stmt = new Sql($this->db);

        if($isStrip){
            foreach ($set as $key => $val) {
                $set[$key] = stripcslashes($val);
            }
        }

        $sql = $stmt->buildSqlString($stmt->insert($table)->values($set, \Zend\Db\Sql\Insert::VALUES_MERGE));
        
        if (!empty($valueFields)) {
            $sql .= " ON DUPLICATE KEY UPDATE ";
            foreach ($valueFields as $field) {
                $sql .= " {$field}=VALUES({$field}),";
            }
        }
        $sql = rtrim($sql, ',');

        return $this->execute($sql, true);
    }
    
    public function insertMulti($table, $set, $valueFields = array(),$isStrip = true)
    {
    	if (empty($set)) {
    		return false;
    	}
    	if (count($set) > 500) {
    		$set1 = array_splice($set, 0, 500);
    		$this->insertMulti($table, $set1, $valueFields, $isStrip);
    		return $this->insertMulti($table, $set, $valueFields, $isStrip);
    	}
        $stmt = new Sql($this->db);
        if($isStrip){
            foreach ($set as $index=>$item) {
                foreach ($item as $key => $val) {
                    $set[$index][$key] = stripcslashes($val);
                }
            }
        }

        $sql = $stmt->buildSqlString($stmt->insert($table)->values(array_shift($set), \Zend\Db\Sql\Insert::VALUES_MERGE));

        foreach ($set as $item) {
            $item_slash = array();
            foreach ($item as $key => $val) {// 对剩下的(array_shift操作之后)$set中的value值进行转义
                $item_slash[$key] = addcslashes($val, "\x00\n\r\\'\"\x1a");
            }
            $sql .= ",('" . implode("','", $item_slash) . "')";
        }
        
        if (!empty($valueFields)) {
            $sql .= " ON DUPLICATE KEY UPDATE ";
            foreach ($valueFields as $field) {
                $sql .= " {$field}=VALUES({$field}),";
            }
        }
        $sql = rtrim($sql, ',');
        
        return $this->execute($sql);
    }
    
    public function update($table, $set, $where, $isStrip = true)
    {
        if (empty($where)) {
            throw new \Exception('update where condition is empty!', -1);
        }
        
        if($isStrip){
            foreach ($set as $key => $val) {
                $set[$key] = stripcslashes($val);
            }
        }
        
        $stmt = new Sql($this->db);
        $sql = $stmt->buildSqlString($stmt->update($table)->set($set)->where($where));
        
        return $this->execute($sql);
    }
    
    public function quoteInto($expression, $parameters)
    {
        $parameters = (array)$parameters;
        $handle = $this->getHandle();
        $expressions = explode('?', $expression);
        $tokens = array();
        foreach ($parameters as $parameter) {
            $tokens[] = array_shift($expressions);
            $tokens[] = $handle->quote($parameter);
        }
        $tokens = array_merge($tokens, $expressions);
        
        return implode(' ', $tokens);
    }
    
    public function fetchRow($sql)
    {
        $result = $this->query($sql);
        return !empty($result) ? $result[0] : $result;
    }
    
    public function fetchAll($sql)
    {
        return $this->query($sql);
    }
    
    /**
     * get PDO instance
     * @return \PDO
     */
    public function getHandle()
    {
        return $this->db->getDriver()->getConnection()->getResource();
    }
    
    public function beginTransaction()
    {
        $this->getHandle()->beginTransaction();
        $this->inTransaction = true;
    }
    
    public function rollBack()
    {
        $this->getHandle()->rollBack();
        $this->inTransaction = false;
    }
    
    public function commit()
    {
        $this->getHandle()->commit();
        $this->inTransaction = false;
        if (isset($this->callbackQueue[self::CALLBACK_TYPE_POSTCOMMIT])) {
        	foreach ($this->callbackQueue[self::CALLBACK_TYPE_POSTCOMMIT] as $key => $val) {
        		list($callback, $params) = $val;
        		Log::info('callback:'.$callback);
        		call_user_func_array($callback, $params);
        		unset($this->callbackQueue[self::CALLBACK_TYPE_POSTCOMMIT][$key]);
        	}
        }
    }
    
    public function isInTransaction()
    {
    	return $this->inTransaction;
    }
    
    public function getConfig()
    {
        return $this->config;
    }

    public function getConnection(){
        return $this->db->getDriver()->getConnection();
    }
    
    public function registerCallback($type, $callback, $params = array())
    {
		$this->callbackQueue[$type][] = array($callback, $params);    	
    }
}