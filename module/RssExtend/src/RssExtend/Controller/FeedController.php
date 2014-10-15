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

    /**
     * @return string
     */
    private function getServerUrl()
    {
        $serverUrl = ((isset($_SERVER['HTTPS']) && mb_strlen($_SERVER['HTTPS']) > 0 && $_SERVER['HTTPS'] != 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
        file_put_contents('data/cache/server-url', $serverUrl);
        return $serverUrl;
    }

    public function updateAction()
    {
        $id = $this->getEvent()->getRouteMatch()->getParam('id');

        /* @var \RssExtend\Feed\Collection $collection */
        $collection = $this->getServiceLocator()->get('RssExtend\Feed\Collection');

        $feed = $collection->getById($id);

        if (null === $feed) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $feed->getUpdatedFeed(true);

        $this->redirect()->toRoute('feed', array(
            'id' => $id, 'action' => 'show'
        ));
    }

    public function showAction()
    {
        $id = $this->getEvent()->getRouteMatch()->getParam('id');

        /* @var \RssExtend\Feed\Collection $collection */
        $collection = $this->getServiceLocator()->get('RssExtend\Feed\Collection');

        $feed = $collection->getById($id);

        if (null === $feed) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $serverUrl = $this->getServerUrl();

        $entries = $feed->getUpdatedFeed();

        return new ViewModel(array(
            'feed' => $feed,
            'serverUrl' => $serverUrl,
            'entries' => $entries
        ));
    }

    public function rssAction()
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
        $xml = $cache->getItem($cacheKey = 'xml' . crc32($feed->getId() . ($time - $time % 3)));

        if (false == $xml) {
            $downloader = $feed->getParser()->getDownloader();
            $downloader->setCache($cache);
            $downloader->setSleep(50000, 500000);
            $feedWriter = $feed->getUpdatedFeed();

            $link = $this->getServerUrl() . $this->url()->fromRoute('feed', array('id' => $feed->getId()));
            $feedWriter->setFeedLink($link, 'rss');

            $xml = $feedWriter->export('rss', true);
            $cache->setItem($cacheKey, $xml);
        }

        $viewModel = new ViewModel(array(
            'xml' => $xml,
        ));
        $viewModel->setTerminal(true);

        $this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/rss+xml; charset=utf-8');

        return $viewModel;
    }
}
