<?php
namespace Application\Includes;

use Application\Exception;

class Error
{
	const ERR_NOT_Implemented = 501;
	const ERR_INTERNAL_ERR = 504;
	const ERR_PARAM_TOKEN = 601;
    const ERR_PARAM_APPID = 602;
	const ERR_PARAM_TOKEN_TIME = 603;
	const ERR_PARAM_TOKEN_SIGN = 604;
	const ERR_PARAM_HEADER = 608;

	const ERR_REQUEST_REFUSED = 700;
	const ERR_LOGIC_COMMON = 701;
	const ERR_DATA_INVALID = 702;
	public static $errMsg = array(
			self::ERR_NOT_Implemented => '请求地址非法',
			self::ERR_INTERNAL_ERR => '系统内部错误',
			self::ERR_PARAM_TOKEN => 'token参数错误',
			self::ERR_PARAM_APPID => 'appid非法',
			self::ERR_PARAM_TOKEN_TIME => 'token时间参数非法',
			self::ERR_PARAM_TOKEN_SIGN => 'token签名验证失败',
			self::ERR_PARAM_HEADER => '请求header错误',
			
			self::ERR_REQUEST_REFUSED => '请求错误，当前数据不允许此操作',
			self::ERR_LOGIC_COMMON => '内部错误',
			self::ERR_DATA_INVALID => '数据非法',

	);
	
	public static function trigger($err_code, $err_msg = '')
	{
		$err_msg = !empty($err_msg) ? $err_msg : (isset(self::$errMsg[$err_code]) ? self::$errMsg[$err_code] : '未知错误');
		throw new Exception($err_msg, $err_code);
	}
}