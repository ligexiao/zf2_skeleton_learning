<?php

namespace Common;

class Util
{
    private static $MAX_FORM_STRING_LEN =255;

    public static function isProductionEnv()
    {
        return ENV == 'production';
    }
    
    public static function isTestEnv()
    {
        return ENV != 'production';
    }
    
    /**
     * Characters encoded are NUL (ASCII 0), \n, \r, \, ', ", and Control-Z.
     * @param string $str
     */
    public static function escape($str)
    {
        // 过滤掉字符串中的HMTL、XML以及PHP标签
        $str = strip_tags($str);
        return addcslashes($str, "\000\n\r\\'\"\032");
    }
    
    /**
     * Escape request params
     * @param unknown $param
     * @return string
     */
    public static function filterRequestParam($param)
    {
    	if (is_array($param)) {
    		foreach ($param as $key => $val) {
    			$param[$key] = self::filterRequestParam($val);
    		}
    	} else {
    		$param = self::escape(trim($param));
    	}
    	
    	return $param;
    }
    
    /**
     * Grep one dimension of two dimension array 
     * @param array $arr
     * @param string $key
     * @return array
     */
    public static function arrayGrep($arr, $key)
    {
        $ret = array();
        foreach ($arr as $val) {
            if (isset($val[$key])) {
                $ret[] = $val[$key];
            } 
        }
        return $ret;
    }
    
    public static function warpValueTextMap($arr, $valField, $textField)
    {
        $map = array();
        foreach ($arr as $val) {
            $map[] = array(
                'value' => $val[$valField],
                'text' => $val[$textField]
            );
        }
        return $map;
    }

    /**
     * 获取时间
     */
    public static function microtime()
    {
    	list($usec, $sec) = explode(' ', microtime());
    	return (float)$usec + (float)$sec;
    }

    /**
     * 字符串验证
     */
    public static function validateString($params, $key){
        if (!isset($params[$key]) ||
            empty($params[$key]) ||
            strlen($params[$key]) > self::$MAX_FORM_STRING_LEN) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * 日期格式验证
     */
    public static function validateDate($date){
        $regex = '/^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/';
        return preg_match($regex,$date);
    }

    /**
     * 正整数验证
     */
    public static function validatePositiveInt($params, $key){
        if (!isset($params[$key]) ||
            empty($params[$key]) ||
            $params[$key]<=0) {
            return false;
        }else{
            return true;
        }
    }

    /***
     * @param $ids
     * $ids, an Integer array, convert it into sql string like (1,2,3,...)
     * @return string
     */
    public static function getIntSqlStr($ids)
    {
        if(count($ids)==0){
            return null;
        }
        $ids_str = " ( ";
        foreach($ids as $c_id)
        {
            $ids_str .= $c_id.",";
        }
        $ids_str = substr($ids_str,0,strlen($ids_str)-1);

        $ids_str .= " ) ";
        return $ids_str;
    }

    /***
     * @param $ids
     * $ids, an String array, convert it into sql string like ('1','2','3',...)
     * @return string
     */
    public static function getStrSqlStr($ids)
    {
        if(count($ids)==0){
            return null;
        }
        $ids_str = " ( ";
        foreach($ids as $c_id)
        {
            $ids_str .= "'".$c_id."',";
        }
        $ids_str = substr($ids_str,0,strlen($ids_str)-1);

        $ids_str .= " ) ";
        return $ids_str;
    }

    public static function ExplodeWithoutNull($delimiter, $string){
        $arr = explode($delimiter,  $string);
        foreach($arr as $index=>$val){
            if(''==$val){
                unset($arr[$index]);
            }
        }
        return array_values($arr);
    }

    /**
     *列出某个目录下的所有文件名,并以数组的形式返回
     *@param:	string	$path 目录路径
     *@return:  array	$list 该目录下的所有文件名
     */
    public static function list_files($path)
    {
        $list = array ();
        if ($dh = opendir($path))
        {
            while (($file = readdir($dh)) != false)
            {
                if (filetype($path . '/' . $file) == 'file')
                {
                    array_push($list, $file);
                }
                else if ($file != '.' && $file != '..')
                {
                    //递归查找目录下的文件
                    $dir = self :: list_files($path . '/' . $file);
                    $list[$file] = $dir;
                }
            }
            closedir($dh);
        }
        return $list;
    }

    /***
     * get the count of months between two date/datetime
    */
    public static function monthDiff($begin_time, $end_time=null){
        $d1 = new \DateTime($begin_time);
        $d2 = new \DateTime($end_time);
        $months = $d1->diff($d2)->m + ($d1->diff($d2)->y)*12;
        return $months;
    }
    
    public static function array2hash($array, $key)
    {
    	$ret = array();
    	foreach ($array as $index => $tmp)
    	{
    		$ret[$tmp[$key]] = $tmp;
    		unset($array[$index]); //删掉原来的数组，防止数组过大造成内存溢出
    	}
    	return $ret;
    }

    // 判断远程文件是否存在
    public static function isUrlExist($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($code == 200){
            $status = true;
        }else{
            $status = false;
        }
        curl_close($ch);
        return $status;
    }

    public static function getHttpType(){
        $type = "http";
        if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443){
            $type = "https";
        }
        return $type;
    }
}