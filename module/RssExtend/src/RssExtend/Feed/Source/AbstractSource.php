<?php
namespace RssExtend\Feed\Source;

use RssExtend\Downloader;
use RssExtend\Feed\Feed;

abstract class AbstractSource
{


    /**
     * @var \Zend\Cache\Storage\Adapter\Filesystem
     */
    private $cache = null;

    /**
     * @var Downloader
     */
    private $downloader = null;

    /**
     * @var Feed
     */
    private $feed = null;

    /**
     * @param Feed $feed
     */
    public function setFeed(Feed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * @return Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }


    /**
     * @param \Zend\Cache\Storage\Adapter\Filesystem $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Downloader $downloader
     */
    public function setDownloader($downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @return Downloader
     */
    public function getDownloader()
    {
        if (null === $this->downloader) {
            $this->setDownloader(new Downloader());
        }

        return $this->downloader;
    }

    abstract public function getRss();

    abstract public function isConfigAvailable();

}
