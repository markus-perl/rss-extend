<?php
namespace RssExtendTest\Feed\PreProcessor;

use RssExtend\Feed\Source\Composer;

class ComposerTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetCache()
    {
        $composer = new Composer();
        $cache = $this->getMock('\Zend\Cache\Storage\Adapter\Filesystem');

        $composer->setCache($cache);
        $this->assertEquals($cache, $composer->getCache());
    }

    public function testSetGetDownloader()
    {
        $composer = new Composer();
        $downloader = $this->getMock('\RssExtend\Downloader');

        $composer->setDownloader($downloader);
        $this->assertEquals($downloader, $composer->getDownloader());
    }

    public function testSetGetFeed()
    {
        $composer = new Composer();
        $feed = $this->getMock('\RssExtend\Feed\Feed');

        $composer->setFeed($feed);
        $this->assertEquals($feed, $composer->getFeed());
    }

    public function testIsConfigAvailable()
    {
        $composer = new Composer();
        $feed = $this->getMock('\RssExtend\Feed\Feed', array('getComposerConfig'));
        $feed->expects($this->at(0))->method('getComposerConfig')->will($this->returnValue(null));
        $feed->expects($this->at(1))->method('getComposerConfig')->will($this->returnValue($this->getMock('\RssExtend\Feed\Config', array(), array(), '',
            false)));

        $composer->setFeed($feed);

        $this->assertFalse($composer->isConfigAvailable());
        $this->assertTrue($composer->isConfigAvailable());
    }

    public function testGetRss()
    {
        $composer = new Composer();

        $config = array(
            'url' => $url = 'http://my.page',
            'dom' => array(
                'teaser' => array(
                    'content' => 'article a'
                )
            )
        );

        $feed = $this->getMock('\RssExtend\Feed\Feed', array('getComposerConfig'));
        $feed->expects($this->any())->method('getComposerConfig')->will($this->returnValue(new \RssExtend\Feed\Config($config)));
        $composer->setFeed($feed);

        $page = file_get_contents(__DIR__ . '/page.html');
        $page2 = file_get_contents(__DIR__ . '/page2.html');

        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->with($url)->will($this->returnValue($page));
        $downloader->expects($this->at(1))->method('download')->with($url)->will($this->returnValue($page2));
        $composer->setDownloader($downloader);

        $cache = $this->getMock('\Zend\Cache\Storage\Adapter\Filesystem', array('getItem', 'setItem'));
        $cache->expects($this->any())->method('getItem')->will($this->returnValue(false));
        $cache->expects($this->any())->method('setItem')->will($this->returnValue(true));
        $composer->setCache($cache);

        $rssXml = $composer->getRss();
        $feed = \Zend\Feed\Reader\Reader::importString($rssXml);
        $this->assertEquals('My Article', $feed->current()->getTitle());
        $this->assertEquals('placeholder', $feed->current()->getDescription());
        $this->assertEquals('http://my.page/to/article', $feed->current()->getLink());

        $rssXml = $composer->getRss();
        $feed = \Zend\Feed\Reader\Reader::importString($rssXml);
        $this->assertEquals('My Article Text', $feed->current()->getTitle());
        $this->assertEquals('placeholder', $feed->current()->getDescription());
        $this->assertEquals('http://my.page/to/article', $feed->current()->getLink());
    }

}
