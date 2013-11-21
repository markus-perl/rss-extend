<?php
namespace RssExtendTest\Feed\Parser;

use RssExtend\Downloader;
use RssExtend\Feed\Feed;
use RssExtend\Feed\Parser\Readability;

class ReadabilityTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetDownloader()
    {
        $feed = new Feed();
        $downloader = new Downloader();
        $readability = new Readability($feed);
        $readability->setDownloader($downloader);
        $this->assertEquals($downloader, $readability->getDownloader());
    }

    public function testFetchFeed()
    {
        $feed = new Feed();
        $feed->setUrl(__DIR__ . '/feed.xml');

        $readability = new Readability($feed);
        $result = $readability->fetchFeed();

        $item = $result->current();
        $this->assertEquals('description', $item->getDescription());
        $this->assertEquals('item 1', $item->getTitle());
    }


    public function testGetContent()
    {
        $feedString = file_get_contents(__DIR__ . '/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/item1.html');
        $downloader = $this->getMock('Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));

        $config = new \Zend\Config\Config(array('token' => 'abc'));

        $feed = new Feed();
        $feed->setUrl('/my/rss');
        $readability = new Readability($feed, $config);
        $readability->setDownloader($downloader);

        $readability->getUpdatedFeed($readability->fetchFeed());
    }

    public function testGetUpdatedFeed()
    {
        $feedString = file_get_contents(__DIR__ . '/feed.xml');
        $testData = '<p>This is a test</p><p>This is another block</p>';
        $downloader = $this->getMock('\RssExtend\Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue(json_encode(array(
                                                                                                         'content' => $testData
                                                                                                    ))));

        $config = new \Zend\Config\Config(array(
                                               'token' => 'abc',
                                          ));

        $feed = new Feed();
        $feed->setUrl(__DIR__ . '/feed.xml');

        $readability = new Readability($feed, $config);
        $readability->setDownloader($downloader);

        $result = $readability->getUpdatedFeed($readability->fetchFeed());
        $current = $result->current();
        $this->assertEquals($testData, $current->getContent());
    }

}
