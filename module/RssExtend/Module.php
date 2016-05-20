<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RssExtend;

use RssExtend\Feed\Collection;
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
        Vagrant::clearCache();

        if ('' == ini_get('date.timezone')) {
            date_default_timezone_set('Europe/Berlin');
        }
        mb_internal_encoding('UTF-8');
        header_remove('X-Powered-By');
        define('DEVELOPMENT', is_dir('/vagrant') || is_file('/tmp/debug'));

        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $e->getTarget()->getEventManager()->attach('dispatch', array($this, 'checkRequirements'), 100);

        $app = $e->getApplication();
        $locator = $app->getServiceManager();
        $this->cache = $locator->get('Zend\Cache\Storage\Adapter\Filesystem');

        if (mt_rand(0, 500) == 1) {
            try {
                $this->cache->clearExpired();
            } catch (\RuntimeException $e) {
            }
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
                    $options->setCacheDir($sm->get('Config')['cacheDir']);
                    $options->setTtl(86400 * 3);

                    $cache = StorageFactory::adapterFactory('filesystem', $options);
                    $plugin = StorageFactory::pluginFactory('exception_handler', array(
                        'throw_exceptions' => true,
                    ));
                    $cache->addPlugin($plugin);
                    return $cache;
                },
                'RssExtend\Host' => function ($sm) {
                    $host = new Host();
                    $host->setCacheDir($sm->get('Config')['cacheDir']);
                    return $host;
                },
                'RssExtend\Feed\Collection' => function ($sm) {
                    $collection = new Collection();
                    $collection->setCache($sm->get('\Zend\Cache\Storage\Adapter\Filesystem'));
                    $collection->setServiceLocator($sm);
                    $collection->fillByConfig($sm->get('RssExtend\Feed\Config'));
                    return $collection;
                },
                'RssExtend\Youtube' => function ($sm) {
                    $youtube = new Youtube();
                    $youtube->setCacheDir($sm->get('Config')['cacheDir']);
                    $youtube->setCache($sm->get('\Zend\Cache\Storage\Adapter\Filesystem'));
                    return $youtube;
                },
            ),
        );
    }
}
