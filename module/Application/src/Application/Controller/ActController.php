<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Model\Activity;
use Common\ReturnInfo;

class ACTController extends BaseController
{
    public function listAction()
    {
        $params['user_id'] = $this->getParam('user_id');
        $params['act_id'] = $this->getParam('act_id');
        $params['page'] = $this->getParam('page', 1);// 第一页
        $params['size'] = $this->getParam('size', 10);// 每页10条

        $act = new Activity();
        $data = $act->user_act_list($params);
        $this->ret->set(new ReturnInfo($act->errCode, $act->errMsg, $data));
    }

    public function updateAction()
    {
        $params['act_id'] = $this->getParam('act_id');
        $params['act_begin_time'] = $this->getParam('act_begin_time');
        $params['act_end_time'] = $this->getParam('act_end_time');
        $params['act_theme'] = $this->getParam('act_theme');
        $params['act_content'] = $this->getParam('act_content');
        $params['act_limit_min'] = $this->getParam('act_limit_min');
        $params['act_limit_max'] = $this->getParam('act_limit_max');

        $act = new Activity();
        $act->update_activity($params);
        $this->ret->set(new ReturnInfo($act->errCode, $act->errMsg));
    }

    public function addAction()
    {
        $params['wechat_id'] = $this->getParam('wechat_id');
        $params['nick_name'] = $this->getParam('nick_name');
        $params['act_begin_time'] = $this->getParam('act_begin_time');
        $params['act_end_time'] = $this->getParam('act_end_time');
        $params['act_theme'] = $this->getParam('act_theme');
        $params['act_content'] = $this->getParam('act_content');
        $params['act_limit_min'] = $this->getParam('act_limit_min');
        $params['act_limit_max'] = $this->getParam('act_limit_max');

        $act = new Activity();
        $act->add_activity($params);
        $this->ret->set(new ReturnInfo($act->errCode, $act->errMsg));
    }

    public function deleteAction()
    {
        $params['act_id'] = $this->getParam('act_id');

        $act = new Activity();
        $act->update_activity($params);
        $this->ret->set(new ReturnInfo($act->errCode, $act->errMsg));
    }

    public function joinAction()
    {
        $params['act_id'] = $this->getParam('act_id');
        $params['user_id'] = $this->getParam('user_id');
        $params['opt_type'] = $this->getParam('opt_type', 1);

        $act = new Activity();
        $act->join_activity($params);
        $this->ret->set(new ReturnInfo($act->errCode, $act->errMsg));
    }
}
