<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RssExtend;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Cache\StorageFactory;

use RssExtend\Requirement\PhpVersion;
use RssExtend\Requirement\CacheWriteable;

class Module
{
    public function onBootstrap (MvcEvent $e)
    {
        if ('' == ini_get('date.timezone')) {
            date_default_timezone_set('Europe/Berlin');
        }

        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->checkRequirements($e);
    }

    public function checkRequirements (MvcEvent $e)
    {
        /*@var \Zend\Cache\Storage\Adapter\Filesystem $cache */
        $cache = $e->getApplication()->getServiceManager()->get('Zend\Cache\Storage\Adapter\Filesystem');
        $requirements = array();

        $requirements[] = new PhpVersion();
        $requirements[] = new CacheWriteable($cache->getOptions()->getCacheDir());

        foreach ($requirements as $requirement) {
            if (false == $requirement->checkRequirement()) {
                exit($requirement->getErrorMessage());
            }
        }

    }

    public function getConfig ()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig ()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig ()
    {
        return array(
            'factories' => array(
                'Zend\Cache\Storage\Adapter\Filesystem' => function ($sm) {
                    $cache = StorageFactory::adapterFactory('filesystem', array(
                                                                               'cache_dir' => __DIR__ . '/../../data/cache'
                                                                          ));
                    $plugin = StorageFactory::pluginFactory('exception_handler', array(
                                                                                      'throw_exceptions' => true,
                                                                                 ));
                    $cache->addPlugin($plugin);
                    return $cache;
                },
            ),
        );
    }
}
