<?php
namespace RssExtendTest\Feed;

use RssExtend\Feed\Feed;

class FeedTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct ()
    {
        $config = new \Zend\Config\Config(array(
                                               'name' => $name = 'test',
                                               'url' => $url = 'http://localhost',
                                               'method' => 'dom'
                                          ));
        $feed = new Feed('test', $config);

        $this->assertEquals($name, $feed->getName());
        $this->assertEquals($url, $feed->getUrl());
        $this->assertInstanceOf('\RssExtend\Feed\Parser\AbstractParser', $feed->getParser());
    }

    /**
     * @expectedException \RssExtend\Feed\Exception\RuntimeException
     */
    public function testConstructInvalidMethod ()
    {

        $config = new \Zend\Config\Config(array(
                                               'name' => $name = 'test',
                                               'url' => $url = 'http://localhost',
                                               'method' => 'beam'
                                          ));
        $feed = new Feed('test', $config);
        $feed->getParser();
    }

    /**
     * @expectedException \RssExtend\Feed\Exception\RuntimeException
     */
    public function testConstructMethodMissing ()
    {

        $config = new \Zend\Config\Config(array(
                                               'name' => $name = 'test',
                                               'url' => $url = 'http://localhost',
                                          ));
        $feed = new Feed('test', $config);
    }


    /**
     * @expectedException \RssExtend\Feed\Exception\RuntimeException
     */
    public function testConstructNameMissing ()
    {

        $config = new \Zend\Config\Config(array(
                                               'url' => $url = 'http://localhost',
                                               'method' => 'dom'
                                          ));
        $feed = new Feed('test', $config);
    }

    /**
     * @expectedException \RssExtend\Feed\Exception\RuntimeException
     */
    public function testConstructUrlMissing ()
    {

        $config = new \Zend\Config\Config(array(
                                               'name' => $name = 'test',
                                               'method' => 'dom'
                                          ));
        $feed = new Feed('test', $config);
    }

    public function testSetGetId ()
    {
        $feed = new Feed();

        $feed->setId($id = 'test');
        $this->assertEquals($id, $feed->getId());
    }

    public function testSetGetName ()
    {
        $feed = new Feed();

        $feed->setName($value = 'test');
        $this->assertEquals($value, $feed->getName());
    }

    public function testSetGetMethod ()
    {
        $feed = new Feed();

        $feed->setMethod($value = 'dom');
        $this->assertEquals($value, $feed->getMethod());
    }

    public function testSetGetPostprocess ()
    {
        $config = new \Zend\Config\Config(array(
                                               'staticImage' => 'http://localhost'
                                          ));
        $feed = new Feed();
        $feed->setPostProcess($config);
        $this->assertEquals($config, $feed->getPostProcess());
    }

    public function testGetPostprocessors ()
    {
        $config = new \Zend\Config\Config(array(
                                               'staticImage' => 'http://localhost'
                                          ));
        $feed = new Feed();
        $feed->setPostProcess($config);

        foreach ($feed->getPostProcessors() as $postProcessor) {
            $this->assertInstanceOf('\RssExtend\Feed\PostProcessor\StaticImage', $postProcessor);
        }
    }


    /**
     * @expectedException \RssExtend\Feed\Exception\RuntimeException
     */
    public function testGetPostprocessorsInvalidName ()
    {
        $config = new \Zend\Config\Config(array(
                                               'notAPostprocessor' => array()
                                          ));
        $feed = new Feed();
        $feed->setPostProcess($config);
        $feed->getPostProcessors();
    }

    public function testGetUpdatedFeed ()
    {
        $feedString = file_get_contents(__DIR__ . '/Parser/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/Parser/item2.html');
        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));
        $downloader->expects($this->at(2))->method('download')->will($this->returnValue($item1Html));


        $config = new \Zend\Config\Config(array(
                                               'name' => $name = 'test',
                                               'url' => $url = 'http://localhost',
                                               'method' => 'dom',
                                               'dom' => array(
                                                   'content' => '.content p',
                                                   'image' => '.image img'
                                               ),
                                               'postProcess' => array(
                                                   'staticImage' => 'http://localhost'
                                               )
                                          ));
        $feed = new Feed('test', $config);
        $feed->getParser()->setDownloader($downloader);

        $result = $feed->getUpdatedFeed();
        $current = $result->current();
        $this->assertEquals('<p><img src="http://localhost/image.jpg"/></p><p>This is a test</p><p>This is another block</p>', $current->getContent());
        $this->assertEquals('Testfeed - rss-extend', $result->getTitle());

        $this->assertEquals('http://localhost/', $result->getLink());
        $this->assertEquals('some test items', $result->getDescription());
    }

    public function testRssExportSlashComments() {
        $feedString = file_get_contents(__DIR__ . '/Parser/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/Parser/item2.html');
        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));
        $downloader->expects($this->at(2))->method('download')->will($this->returnValue($item1Html));


        $config = new \Zend\Config\Config(array(
                                               'name' => $name = 'test',
                                               'url' => $url = 'http://localhost',
                                               'method' => 'dom',
                                               'dom' => array(
                                                   'content' => '.content p',
                                                   'image' => '.image img'
                                               ),
                                               'postProcess' => array(
                                                   'staticImage' => 'http://localhost'
                                               )
                                          ));
        $feed = new Feed('test', $config);
        $feed->getParser()->setDownloader($downloader);

        $result = $feed->getUpdatedFeed();
        $xml = $result->export('rss');

        $this->assertEquals(0, substr_count('slash:comments', $xml));
    }

    public function testSetGetCache ()
    {
        $feed = new Feed();
        $feed->setMethod('dom');
        $feed->setCache($mock = $this->getMock('\Zend\Cache\Storage\Adapter\Filesystem'));
        $this->assertEquals($mock, $feed->getCache());

        $this->assertEquals($mock, $feed->getParser()->getDownloader()->getCache());
    }

    public function testRssExportMedia() {
        $feedString = file_get_contents(__DIR__ . '/Parser/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/Parser/item2.html');
        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));
        $downloader->expects($this->at(2))->method('download')->will($this->returnValue($item1Html));

        $imageSize = new \RssExtend\ImageSize();
        $imageSize->setDownloader($downloader);

        $config = new \Zend\Config\Config(array(
                                               'name' => $name = 'test',
                                               'url' => $url = 'http://localhost',
                                               'method' => 'dom',
                                               'dom' => array(
                                                   'content' => '.content p',
                                                   'image' => '.image img'
                                               ),
                                               'postProcess' => array(
                                                   'staticImage' => 'http://localhost'
                                               )
                                          ));
        $feed = new Feed('test', $config);
        $feed->getParser()->setDownloader($downloader);
        $feed->getParser()->setImageSize($imageSize);

        $result = $feed->getUpdatedFeed();
        $xml = $result->export('rss');

        $this->assertContains('<media:thumbnail url="http://localhost/image.jpg" width="100" height="100"/>', $xml);
    }

}
