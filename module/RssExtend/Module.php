<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RssExtend;

use Zend\Cache\Storage\Adapter\FilesystemOptions;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Cache\StorageFactory;

use RssExtend\Requirement\PhpVersion;
use RssExtend\Requirement\CacheWriteable;
use RssExtend\Requirement\ModCurl;


class Module
{

    protected $cache;


    public function onBootstrap(MvcEvent $e)
    {
        if ('' == ini_get('date.timezone')) {
            date_default_timezone_set('Europe/Berlin');
        }
        mb_internal_encoding('UTF-8');
        header_remove('X-Powered-By');

        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $e->getTarget()->getEventManager()->attach('dispatch', array($this, 'checkRequirements'), 100);

        $app = $e->getApplication();
        $locator = $app->getServiceManager();
        $this->cache = $locator->get('Zend\Cache\Storage\Adapter\Filesystem');

        if (mt_rand(0, 100) == 1) {
            $this->cache->clearExpired();
        }

    }

    public function checkRequirements(MvcEvent $e)
    {
        /*@var \Zend\Cache\Storage\Adapter\Filesystem $cache */
        $cache = $e->getApplication()->getServiceManager()->get('Zend\Cache\Storage\Adapter\Filesystem');
        $requirements = array();

        $requirements[] = new PhpVersion();
        $requirements[] = new CacheWriteable($cache->getOptions()->getCacheDir());
        $requirements[] = new ModCurl();

        foreach ($requirements as $requirement) {
            if (false == $requirement->checkRequirement()) {
                exit($requirement->getErrorMessage());
            }
        }

    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Zend\Cache\Storage\Adapter\Filesystem' => function ($sm) {

                        $options = new FilesystemOptions();
                        $options->setCacheDir(__DIR__ . '/../../data/cache/');
                        $options->setTtl(86400);

                        $cache = StorageFactory::adapterFactory('filesystem', $options);
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
