<?php
namespace RssExtendTest\Feed\PreProcessor;

use RssExtend\Feed\Source\Rss;

class RssTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetCache()
    {
        $rss = new Rss();
        $cache = $this->getMock('\Zend\Cache\Storage\Adapter\Filesystem');

        $rss->setCache($cache);
        $this->assertEquals($cache, $rss->getCache());
    }

    public function testSetGetDownloader()
    {
        $rss = new Rss();
        $downloader = $this->getMock('\RssExtend\Downloader');

        $rss->setDownloader($downloader);
        $this->assertEquals($downloader, $rss->getDownloader());
    }

    public function testSetGetFeed()
    {
        $rss = new Rss();
        $feed = $this->getMock('\RssExtend\Feed\Feed');

        $rss->setFeed($feed);
        $this->assertEquals($feed, $rss->getFeed());
    }

    public function testIsConfigAvailable() {
        $rss = new Rss();
        $feed = $this->getMock('\RssExtend\Feed\Feed', array('getUrl'));
        $feed->expects($this->at(0))->method('getUrl')->will($this->returnValue('http://feed.url'));
        $feed->expects($this->at(1))->method('getUrl')->will($this->returnValue(null));

        $rss->setFeed($feed);

        $this->assertTrue($rss->isConfigAvailable());
        $this->assertFalse($rss->isConfigAvailable());
    }

    public function testGetRss() {
        $rss = new Rss();

        $feed = $this->getMock('\RssExtend\Feed\Feed', array('getUrl'));
        $feed->expects($this->at(0))->method('getUrl')->will($this->returnValue($url = 'http://feed.url'));

        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->once())->method('download')->with($url)->will($this->returnValue($xml = '<xml></xml>'));

        $rss->setFeed($feed);
        $rss->setDownloader($downloader);

        $rssXml = $rss->getRss();
        $this->assertEquals($xml, $rssXml);

    }
}
