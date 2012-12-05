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
}
