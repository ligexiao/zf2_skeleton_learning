<?php

namespace Common;

use Zend\Log\Logger;

class Log
{
    private static $logger;
    private static $verboseFlag = false;

    public static function registerLog($logPath, $format = '')
    {
        self::$logger = new Logger();
        $format = !empty($format) ? $format : "%timestamp% %priorityName%: %message%";

        $oldmask = umask(0);
        $writer = new \Zend\Log\Writer\Stream($logPath);
        umask($oldmask);
        $writer->setFormatter(new \Zend\Log\Formatter\Simple($format));

        self::$logger->addWriter($writer);
    }

    public static function initFileLog($logPath, $format = '')
    {
        self::$logger = new Logger();
        $format = !empty($format) ? $format : "%timestamp% %priorityName%: %message%";
        
        $oldmask = umask(0);
        $writer = new \Zend\Log\Writer\Stream($logPath);
        umask($oldmask);
        $writer->setFormatter(new \Zend\Log\Formatter\Simple($format));

        self::$logger->addWriter($writer);
    }
    
    public static function getLogFileByControllerAction($logPath, $controller, $action)
    {
        $logPath = $logPath . '/' . date('Ym');
        if (!file_exists($logPath)) {
        	$oldmask = umask(0);
            mkdir($logPath, 0777, true);
            umask($oldmask);
        }
        
        return $logPath . '/' . $controller . '_' . $action . '.log';
    }

    public static function info($msg)
    {
    	if (!empty(self::$logger)) {
			self::$logger->info($msg);
    	}

        if (self::$verboseFlag) {
            echo $msg . PHP_EOL;
        }
    }
    
    public static function err($msg)
    {
    	if (!empty(self::$logger)) {
			self::$logger->err($msg);
    	}

        if (self::$verboseFlag) {
            echo $msg . PHP_EOL;
        }
    }
    
    public static function debug($msg)
    {
    	if (!empty(self::$logger)) {
			self::$logger->debug($msg);
    	}

        if (self::$verboseFlag) {
            echo $msg . PHP_EOL;
        }
    }
    
    public static function setVerbose($flag)
    {
        self::$verboseFlag = $flag;
    }
}