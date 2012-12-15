<?php
namespace RssExtendTest\Controller;
use RssExtend\Controller\IndexController;
use RssExtend\Downloader;
use RssExtend\Feed\Collection;
use Zend\Stdlib\ArrayUtils;


class IndexControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDownloader ()
    {
        $sm = \RssExtendTest\Bootstrap::getServiceManager();

        /* @var Downloader $downloader */
        $downloader = $sm->get('RssExtend\Downloader');
        $this->assertInstanceOf('RssExtend\Downloader', $downloader);
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Filesystem', $downloader->getCache());
    }

    public function testGetFeedConfig ()
    {
        $sm = \RssExtendTest\Bootstrap::getServiceManager();

        /* @var Downloader $downloader */
        $config = $sm->get('RssExtend\Feed\Config');
        $this->assertInstanceOf('RssExtend\Feed\Config', $config);

        $config = $config->toArray();

        $this->assertArrayHasKey('feed1', $config);
        $this->assertArrayHasKey('feed2', $config);

        $this->assertEquals(array(
                                 'name' => 'Feed 1',
                                 'url' => 'http://localhost/feed1',
                                 'method' => 'dom',
                                 'postProcess' => array('staticImage' => 'http://localhost')
                            ), $config['feed1']);

        $this->assertEquals(array(
                                 'name' => 'Feed 2',
                                 'url' => 'http://localhost/feed2',
                                 'method' => 'trim'
                            ), $config['feed2']);
    }

    public function testCollection ()
    {
        $sm = \RssExtendTest\Bootstrap::getServiceManager();

        /* @var Collection $downloader */
        $collection = $sm->get('RssExtend\Feed\Collection');
        $this->assertInstanceOf('RssExtend\Feed\Collection', $collection);
        $this->assertEquals(2, $collection->count());

        $this->assertNotNull($collection->getCache());

    }

}
