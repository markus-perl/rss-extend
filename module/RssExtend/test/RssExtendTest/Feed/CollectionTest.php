<?php
namespace RssExtendTest\Feed;
use RssExtend\Feed\Collection;
use RssExtend\Feed\Feed;
use RssExtend\Feed\Config;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAddElement ()
    {
        $collection = new Collection();
        $collection->addElement($feed = new Feed());

        foreach ($collection as $entry) {
            $this->assertEquals($feed, $entry);
        }
    }

    public function testFillByConfig ()
    {
        $config = new Config(__DIR__ . '/testData');

        $collection = new Collection();
        $collection->setCache($cache = $this->getMock('\Zend\Cache\Storage\Adapter\Filesystem'));
        $collection->setServiceLocator($this->getMock('\Zend\ServiceManager\ServiceLocatorInterface'));
        $collection->fillByConfig($config);

        $this->assertEquals(2, $collection->count());

        foreach ($collection as $feed) {
            $this->assertInstanceOf('RssExtend\Feed\Feed', $feed);
            $this->assertNotNull($feed->getName());
            $this->assertNotNull($feed->getId());
            $this->assertEquals($cache, $feed->getCache());
        }
    }

    public function testgetById ()
    {
        $config = new Config(__DIR__ . '/testData');

        $collection = new Collection();
        $collection->setServiceLocator($this->getMock('\Zend\ServiceManager\ServiceLocatorInterface'));
        $collection->fillByConfig($config);

        $feed1 = $collection->getById('feed1');
        $this->assertNotNull($feed1);
        $this->assertEquals('feed1', $feed1->getId());

        $feed2 = $collection->getById('feed2');
        $this->assertNotNull($feed2);
        $this->assertEquals('feed2', $feed2->getId());
    }

    public function testSort ()
    {
        $feed1 = new Feed();
        $feed1->setName('B');

        $feed2 = new Feed();
        $feed2->setName('A');

        $collection = new Collection();
        $collection->addElement($feed1);
        $collection->addElement($feed2);

        $collection->sort();

        $this->assertEquals($feed2, $collection->current());
    }

    public function testSetGetCache ()
    {
        $collection = new Collection();
        $collection->setCache($mock = $this->getMock('\Zend\Cache\Storage\Adapter\Filesystem'));
        $this->assertEquals($mock, $collection->getCache());
    }

}
