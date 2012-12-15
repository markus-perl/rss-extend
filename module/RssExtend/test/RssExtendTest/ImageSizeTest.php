<?php
namespace RssExtendTest;

use RssExtend\ImageSize;

class ImageSizeTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetCache ()
    {
        $imageSize = new ImageSize;

        $cache = $this->getMock('\Zend\Cache\Storage\Adapter\Filesystem');

        $imageSize->setCache($cache);
        $this->assertEquals($cache, $imageSize->getCache());
    }


    public function testSetGetDownloader ()
    {
        $imageSize = new ImageSize;

        $downloader = $this->getMock('\RssExtend\Downloader');

        $imageSize->setDownloader($downloader);
        $this->assertEquals($downloader, $imageSize->getDownloader());
    }

    public function testGetSizeByUrl ()
    {
        $imageSize = new ImageSize;
        $downloader = new \RssExtend\Downloader();

        $imageSize->setDownloader($downloader);
        $result = $imageSize->getSizeByUrl(__DIR__ . '/test.png');

        $this->assertEquals(array(
                                 'x' => 15,
                                 'y' => 15
                            ), $result);

    }
}
