<?php
/**
 * Zend Framework (http://framework.zend.com/)
*
* @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
* @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
* @license   http://framework.zend.com/license/new-bsd New BSD License
*/

namespace Application\Controller;

use Common\DBOperFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Common\ReturnInfo;
use Common\Log;
use Common\Util;
use Application\Exception;


class BaseController extends AbstractActionController
{
    /**
     * @var ViewModel
     */
    protected $view;

    /**
     * Name of contrller
     * @var string
     */
    protected $controller;

    /**
     * Name of action
     * @var string
     */
    protected $action;

    protected $defaultView = true;

    protected $isJsonActionFlag = true;


    /**
     * 是否输出全整页面
     * @var boolean
     */
    protected $fullPage = true;

    public $ret;

    public function onDispatch(MvcEvent $e)
    {
        if (empty($this->controller)) {
            $routeMatch = $e->getRouteMatch();
            $this->controller = $routeMatch->getParam('controller', '');
            $sub_index = 0;
            if (strrpos($this->controller, '\\') ){
                $sub_index = strrpos($this->controller, '\\') + 1;
            }
            $this->controller = strtolower(substr($this->controller, $sub_index));
            $this->action = strtolower($routeMatch->getParam('action', ''));

            //404优先处理，
            //避免产生很多脏的log文件
            $method = static::getMethodFromAction($this->action);
            if (!method_exists($this, $method)) {
            	if (isset($_SESSION['uin'])) {
            		$this->action = '404';
            	} else {
            		$this->redirectToUrl('/');
            	}
            }
            
            //init for all controller and action
            $this->view = new ViewModel();
            $this->layout()->controller = $this->controller;
            $this->layout()->action = $this->action;

            //Overload this param if not json aciton
            if ($this->isJsonActionFlag) {
                $this->isJsonAction();
            }else{
            	$this->ret = new ReturnInfo();
            }

            //init log
            $user = isset($_SESSION['uin']) ? $_SESSION['uin'] : '';
            $addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
            $format = "%timestamp% %priorityName%: [{$addr}] [{$user}] %message%";
            $namespace = substr(__NAMESPACE__, 0, strpos(__NAMESPACE__, '\\'));

            $logPath = Log::getLogFileByControllerAction($this->getConfig('logpath'), $namespace.'_'.$this->controller, $this->action);

            // 注册日志存储介质
            Log::registerLog($logPath, $format);

            Log::info('request begin...');

            //init db config
            DBOperFactory::setConfig($this->getConfig('dbconfig'));
        }

        try {
            $this->preDispatch();
            $response = parent::onDispatch($e);
        } catch (Exception $ex) {
			Log::info('dispatch exception:' . $ex->getMessage());
			Log::info($ex->getTraceAsString());
            if ($this->isJsonActionFlag) {
            	$code = $ex->getCode();
                $this->ret->set(new ReturnInfo($code > 0 ? -1 : $code, $ex->getMessage()));
            } else {
                throw $ex;
            }
        }

        if ($this->defaultView) {
            //If don't want use default view
            //Overload the onDispatch and set defaultView to false
            $e->setResult($this->view);
            return $this->view;
        } else {
            return $response;
        }
    }
    
    protected function preDispatch()
    {
    	//overload in controller class if needed
    }

    protected function getService($serviceName)
    {
        return $this->getServiceLocator()->get($serviceName);
    }

    protected function redirectToUrl($url)
    {
        header('Location:' . $url);
        exit();
    }

    protected function getConfig($key)
    {
    	return !empty($key) ? \App::getConfig($key) : '';
    }

    protected function isJsonAction()
    {
        header('Content-Type: application/json');
        $this->view->setTerminal(true);
        $this->view->setTemplate('application/json');
        $this->ret = new ReturnInfo();
        $this->view->ret = $this->ret;
    }

    protected function getParam($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return Util::filterRequestParam($_POST[$key]);
        } elseif (isset($_GET[$key])) {
            return Util::filterRequestParam($_GET[$key]);
        } else {
            return $default;
        }
    }

    public function getParams()
    {
        $params = array_merge($_POST, $_GET);
        return Util::filterRequestParam($params);
    }

    public function __destruct()
    {
        Log::info('request end...');
    }
}
