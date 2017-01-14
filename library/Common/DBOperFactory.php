<?php

namespace Common;

class DBOperFactory
{
    protected static $config = array();
    
    protected static $instanceContainer = array();

    public function __construct()
    {
    }
    
    public static function setConfig($config)
    {
        self::$config = $config;
    }
    
    /**
     * Get DBOper instance by db name
     * @param string $dbname
     * @throws \Exception
     * @return DBOper
     */
    public static function getDbInstanceByName($dbname)
    {
        if (isset(self::$config[$dbname])) {
            if (!isset(self::$instanceContainer[$dbname])) {
                self::$instanceContainer[$dbname] = new DBOper(self::$config[$dbname]);
            }
            return self::$instanceContainer[$dbname];
        } else {
            throw new \Exception("{$dbname}'s config dose not exists", -1);
        }
    }
    
    /**
     * Get DBOper instance 
     * @throws \Exception
     * @return DBOper
     */
    public static function getDb()
    {
        return self::getDbInstanceByName('yue');
    }
}