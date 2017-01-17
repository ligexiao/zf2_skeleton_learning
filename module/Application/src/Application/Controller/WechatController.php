<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Model\Login;
use Common\ReturnInfo;

class WechatController extends BaseController
{
    public function onLoginAction()
    {
        $params['code'] = $this->getParam('code');

        $act = new Login();
        $data = $act->get_login_session($params);
        $this->ret->set(new ReturnInfo($act->errCode, $act->errMsg, $data));
    }

    public function sessionKeyAction()
    {
        $params['trd_session_key'] = $this->getParam('trd_session_key');

        $act = new Login();
        $data = $act->get_session_key($params);
        $this->ret->set(new ReturnInfo($act->errCode, $act->errMsg, $data));
    }
}
