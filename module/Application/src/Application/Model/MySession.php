<?php
namespace Application\Model;

use Application\Includes\Error;
use Common\DBOperFactory;
use Common\Log;
use Common\WxAPI;

class MySession extends AbstractModel
{
    public $errCode= 0;
    public $errMsg = 'ok';

    public function __construct()
    {
        $this->db = DBOperFactory::getDb();
    }

    public function get_login_key($params)
    {
        if(empty($params) || !isset($params['code'])){
            $this->errCode = -1;
            $this->errMsg = "code参数不能为空";
            return null;
        }

        $appid = \App::getConfig('appid');
        $secret = \App::getConfig('secret');
        $api = new WxAPI($appid, $secret);
        $url = "https://api.weixin.qq.com/sns/jscode2session";
        $data = array(
            'js_code'=>$params['code'],
            'grant_type' => 'authorization_code'
        );
        $ret = $api->request($url, $data);
        Log::info("get_login_key-request-ret: {$ret}");
        $ret =  json_encode($ret, true);
        if(!empty($ret) && !empty($ret['openid']) && !empty($ret['session_key'])){
            return $ret;
        }elseif(!empty($ret) && isset($ret['errcode']) && 40029==$ret['errcode']){
            $this->errCode = -2;
            $this->errMsg = "invalid code";
            return null;
        }else{
            $this->errCode = -3;
            $this->errMsg = "未知错误";
            return null;
        }
    }

}
