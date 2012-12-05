<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RssExtend\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use RssExtend\Feed;

class FeedController extends AbstractActionController
{
    public function showAction ()
    {
        $id = $this->getEvent()->getRouteMatch()->getParam('id');

        /* @var \RssExtend\Feed\Collection $collection */
        $collection = $this->getServiceLocator()->get('RssExtend\Feed\Collection');

        $feed = $collection->getById($id);

        if (null === $feed) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $serverUrl = ((isset($_SERVER['HTTPS'])) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];

        return new ViewModel(array(
                                  'feed' => $feed,
                                  'serverUrl' => $serverUrl
                             ));
    }

    public function rssAction ()
    {
        $id = $this->getEvent()->getRouteMatch()->getParam('id');

        /* @var \RssExtend\Feed\Collection $collection */
        $collection = $this->getServiceLocator()->get('RssExtend\Feed\Collection');

        $feed = $collection->getById($id);

        if (null === $feed) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        /* @var \Zend\Cache\Storage\Adapter\Filesystem $cache */
        $cache = $this->getServiceLocator()->get('Zend\Cache\Storage\Adapter\Filesystem');

        $time = time();
        $xml = $cache->getItem($cacheKey = 'xml' . crc32($feed->getId() . ($time - $time % 600)));

        if (false == $xml) {
            $feed->getParser()->getDownloader()->setCache($cache);
            $xml = $feed->getParser()->getUpdatedFeed()->export('rss', true);
            $cache->setItem($cacheKey, $xml);
        }

        $viewModel = new ViewModel(array(
                                        'xml' => $xml,
                                   ));
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function previewAction ()
    {
        $id = $this->getEvent()->getRouteMatch()->getParam('id');

        /* @var \RssExtend\Feed\Collection $collection */
        $collection = $this->getServiceLocator()->get('RssExtend\Feed\Collection');

        $feed = $collection->getById($id);

        if (null === $feed) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        /* @var \Zend\Cache\Storage\Adapter\Filesystem $cache */
        $cache = $this->getServiceLocator()->get('Zend\Cache\Storage\Adapter\Filesystem');

        $feed->getParser()->getDownloader()->setCache($cache);
        $entries = $feed->getParser()->getUpdatedFeed();


        return new ViewModel(array(
                                  'feed' => $feed,
                                  'entries' => $entries,
                             ));
    }
}
