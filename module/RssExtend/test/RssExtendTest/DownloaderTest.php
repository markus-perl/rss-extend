<?php
namespace RssExtendTest;

use RssExtend\Downloader;

class DownloaderTest extends \PHPUnit_Framework_TestCase
{

    public function testDownload ()
    {
        $downloader = new Downloader();

        $content = $downloader->download(__DIR__ . '/test.txt');
        $this->assertEquals('test', $content);
    }

    public function testCacheEmpty ()
    {
        $downloader = new Downloader();

        $cache = $this->getMock('Zend\Cache\Storage\Adapter\Filesystem', array(
                                                                                       'getItem',
                                                                                       'setItem'
                                                                                  ));
        $cache->expects($this->any())->method('getItem')->will($this->returnValue(null));
        $cache->expects($this->once())->method('setItem')->with('url2919025268', 'test');

        $downloader->setCache($cache);

        $this->assertEquals('test', $downloader->download(__DIR__ . '/test.txt'));
    }

    public function testCacheHit ()
    {
        $downloader = new Downloader();

        $cache = $this->getMock('Zend\Cache\Storage\Adapter\Filesystem', array(
                                                                                       'getItem',
                                                                                       'setItem'
                                                                                  ));
        $cache->expects($this->any())->method('getItem')->will($this->returnValue('test'));
        $cache->expects($this->never())->method('setItem');

        $downloader->setCache($cache);

        $this->assertEquals('test', $downloader->download(__DIR__ . '/test.txt'));
    }

    public function testCacheDisable() {
        $downloader = new Downloader();

        $cache = $this->getMock('Zend\Cache\Storage\Adapter\Filesystem', array(
                                                                              'getItem',
                                                                              'setItem'
                                                                         ));
        $cache->expects($this->never())->method('getItem');
        $cache->expects($this->never())->method('setItem');

        $downloader->setCache($cache);

        $this->assertEquals('test', $downloader->download(__DIR__ . '/test.txt', false));
    }
}
