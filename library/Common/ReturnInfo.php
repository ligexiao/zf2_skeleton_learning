<?php

namespace Common;

/**
 * Json return content template
 */
class ReturnInfo
{
    public $return_code;
    public $return_msg;
    
    const CODE_COMMON_ERR = -1;
    const CODE_SUCCESS = 0;
    
    public function __construct($ret_code = 0, $ret_msg = '')
    {
        $this->return_code = $ret_code;
        $this->return_msg = $ret_msg;
    }
    
    public function toJson()
    {
        return json_encode(array('return_code' => $this->return_code, 'return_msg' => $this->return_msg));
    }
    
    public function set(ReturnInfo $ret)
    {
        $this->return_code = $ret->return_code;
        $this->return_msg = $ret->return_msg;
    }
    
    public function isSuccess()
    {
        return $this->return_code == self::CODE_SUCCESS;
    }
}