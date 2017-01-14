<?php
namespace Common;

/**
 * Json return content template
 */
class  ReturnInfo
{
    public $return_code;
    public $return_msg;
    public $data;

    const CODE_COMMON_ERR = -1;
    const CODE_SUCCESS = 0;

    public function __construct($ret_code = 0, $ret_msg = '', $data = null)
    {
        $this->return_code = $ret_code;
        $this->return_msg = $ret_msg;
        if($data !== null){
            $this->data = $data;
        }
    }

    public function toJson()
    {
        $ret = array(
            'return_code' => $this->return_code,
            'return_msg' => $this->return_msg
        );
        if($this->data !== null){
            $ret['data'] = $this->data;
        }
        return json_encode($ret);
    }

    public function set(ReturnInfo $ret)
    {
        $this->return_code = $ret->return_code;
        $this->return_msg = $ret->return_msg;
        if(isset($ret->data)){
            $this->data = $ret->data;;
        }
    }

    public function isSuccess()
    {
        return $this->return_code == self::CODE_SUCCESS;
    }
}