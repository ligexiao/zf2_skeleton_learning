<?php
namespace Application\Model;

use Application\Includes\Error;
use Common\DBOperFactory;
use Common\Log;
use Common\Session;
use Common\WxAPI;

class Login extends AbstractModel
{
    public $errCode= 0;
    public $errMsg = 'ok';

    public function __construct()
    {
        $this->db = DBOperFactory::getDb();
    }

    /***
     * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
     *  其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
     * https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code
     */
    public function get_login_session($params)
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
        Log::info("get_login_key-request-ret: ".json_encode($ret));
        if(!empty($ret) && 0==$ret['code'] && !empty($ret['data'])){
            $ret_data = $ret['data'];
            if(isset($ret_data) && !empty($ret_data['openid'])&& !empty($ret_data['session_key'])){
                $sessions= Session::set_session($ret_data['openid'], $ret_data['session_key']);
                return $sessions;
            }elseif(isset($ret_data) && !empty($ret_data['errcode'])){
                $this->errCode = -1*$ret_data['errcode'];
                $this->errMsg = $ret['errmsg'];
                return $ret_data;
            }else{
                $this->errCode = -4;
                $this->errMsg = "微信返回未定义错误";
                return null;
            }
        }elseif(!empty($ret) && $ret['code'] < 0){
            $this->errCode = -2;
            $this->errMsg = $ret['msg'];
            return null;
        }else{
            $this->errCode = -3;
            $this->errMsg = "未知错误";
            return null;
        }
    }

    /**
     * 获取微信session key
    */
    public function get_session_key($params){
        if(empty($params) || !isset($params['trd_session_key'])){
            $this->errCode = -1;
            $this->errMsg = "trd_session_key参数不能为空";
            return null;
        }

        $sessions = Session::set_session(null, null, $params['trd_session_key']);
        return $sessions;
    }

}
