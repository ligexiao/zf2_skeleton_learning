<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Common\ReturnInfo;
use Zend\View\Model\ViewModel;

class TestController extends BaseController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function stringAction()
    {
        echo "hello zendframework2";exit;
    }

    public function jsonAction()
    {
        $this->ret->set(new ReturnInfo(0, 'ok'));
    }
}
