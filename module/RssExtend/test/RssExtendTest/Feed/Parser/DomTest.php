<?php
namespace RssExtendTest\Feed\Parser;
use \RssExtend\Feed\Parser\Dom;
use \RssExtend\Feed\Feed;
use \RssExtend\Downloader;

class DomTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetDownloader ()
    {
        $feed = new Feed();
        $downloader = new Downloader();
        $dom = new Dom($feed);
        $dom->setDownloader($downloader);
        $this->assertEquals($downloader, $dom->getDownloader());
    }

    public function testFetchFeed ()
    {

        $feed = new Feed();
        $feed->setUrl(__DIR__ . '/feed.xml');

        $dom = new Dom($feed);
        $result = $dom->fetchFeed();

        $item = $result->current();
        $this->assertEquals('description', $item->getDescription());
        $this->assertEquals('item 1', $item->getTitle());
    }

    /**
     * @expectedException \RssExtend\Feed\Parser\Exception\RuntimeException
     */
    public function testGetContent ()
    {

        $feedString = file_get_contents(__DIR__ . '/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/item1.html');
        $downloader = $this->getMock('Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));

        $config = new \Zend\Config\Config(array());

        $feed = new Feed();
        $feed->setUrl('/my/rss');
        $dom = new Dom($feed, $config);
        $dom->setDownloader($downloader);

        $result = $dom->getUpdatedFeed($dom->fetchFeed());
    }

    public function testGetUpdatedFeed ()
    {
        $feedString = file_get_contents(__DIR__ . '/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/item1.html');
        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));


        $config = new \Zend\Config\Config(array(
                                               'content' => '.content p',
                                          ));

        $feed = new Feed();
        $feed->setUrl(__DIR__ . '/feed.xml');

        $dom = new Dom($feed, $config);
        $dom->setDownloader($downloader);

        $result = $dom->getUpdatedFeed($dom->fetchFeed());
        $current = $result->current();
        $this->assertEquals('<p>This is a test</p><p>This is another block</p>', $current->getContent());
    }

    public function testGetUpdatedFeedWithImage ()
    {
        $feedString = file_get_contents(__DIR__ . '/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/item1.html');
        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));
        $downloader->expects($this->at(2))->method('download')->will($this->returnValue($item1Html));


        $config = new \Zend\Config\Config(array(
                                               'content' => '.content p',
                                               'image' => '.image img',
                                          ));

        $feed = new Feed();
        $feed->setUrl(__DIR__ . '/feed.xml');

        $dom = new Dom($feed, $config);
        $dom->setDownloader($downloader);

        $result = $dom->getUpdatedFeed($dom->fetchFeed());
        $current = $result->current();
        $this->assertEquals('<p><img class="rssextend" src="http://localhost.de/image.jpg"/></p><p>This is a test</p><p>This is another block</p>', $current->getContent());
    }

    public function testStripTags ()
    {
        $feedString = file_get_contents(__DIR__ . '/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/item3.html');
        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));


        $config = new \Zend\Config\Config(array(
                                               'content' => '.content p',
                                          ));

        $feed = new Feed();
        $feed->setUrl(__DIR__ . '/feed.xml');

        $dom = new Dom($feed, $config);
        $dom->setDownloader($downloader);

        $result = $dom->getUpdatedFeed($dom->fetchFeed());
        $current = $result->current();
        $this->assertEquals('<p>This is <br/>a test.</p>', $current->getContent());
    }

}
