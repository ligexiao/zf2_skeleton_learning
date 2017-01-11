<?php
/**
 * 创建抽象工厂,自动注册应用程序的controller类
 *  避免开发人员忘记手动在module.config.php的invokables处注册.
 *
 * From: https://samsonasik.wordpress.com/2012/12/23/zend-framework-2-automatic-controller-invokables-via-abstract-factories/
 *
 * 关于Zend框架的Service manager的抽象工厂(abstract_factories):
 *  An abstract factory can be considered a “fallback”
 *  – if the service(‘YourModule\Controller\A’ is a controller service) does not exist in the ServiceManager,
 *      it will then pass it to any abstract factories attached to it until one of them is able to return an object.
 */

namespace Application\Services;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CommonControllerAbstractFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        if (class_exists($requestedName.'Controller')){
            return true;
        }

        return false;
    }

    public function createServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        $class = $requestedName.'Controller';
        return new $class;
    }
}
