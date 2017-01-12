<?php

class App
{
	private static $config;
	/**
	 * @var Zend\Mvc\Application
	 */
	public static $application;
	
	public static function set($application)
	{
		self::$application = $application;
	}

	/**
	 * @return Zend\Mvc\Application
	 */
	public static function get()
	{
		return self::$application;
	}

	public static function getConfig($key = null)
	{
		if (empty($key)) {
			return self::$application->getConfig();
		} else {
			if (isset(self::$config[$key])) {
				return self::$config[$key];	
			} else {
				$config = self::$application->getConfig();
				return isset($config[$key]) ? $config[$key] : null;
			}
		}
	}
}