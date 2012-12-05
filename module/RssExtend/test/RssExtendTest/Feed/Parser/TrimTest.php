<?php
namespace RssExtendTest\Feed\Parser;
use \RssExtend\Feed\Parser\Trim;
use \RssExtend\Feed\Feed;
use \RssExtend\Downloader;

class TrimTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetDownloader ()
    {
        $feed = new Feed();
        $downloader = new Downloader();
        $dom = new Trim($feed);
        $dom->setDownloader($downloader);
        $this->assertEquals($downloader, $dom->getDownloader());
    }

    public function testGetUpdatedFeed ()
    {

        $feedString = file_get_contents(__DIR__ . '/feed.xml');
        $item1Html = file_get_contents(__DIR__ . '/item1.html');
        $downloader = $this->getMock('Downloader', array('download'));
        $downloader->expects($this->at(0))->method('download')->will($this->returnValue($feedString));
        $downloader->expects($this->at(1))->method('download')->will($this->returnValue($item1Html));


        $config = new \Zend\Config\Config(array(
                                               'from' => array(
                                                   'searchText' => '<div class="content">',
                                                   'offset' => 0,
                                                   'direction' => 'top-to-bottom'
                                               ),
                                               'to' => array(
                                                   'searchText' => '</div>',
                                                   'offset' => 0,
                                                   'direction' => 'top-to-bottom'
                                               )
                                          ));

        $feed = new Feed();
        $feed->setUrl(__DIR__ . '/feed.xml');

        $dom = new Trim($feed, $config);
        $dom->setDownloader($downloader);

        $result = $dom->getUpdatedFeed();
        $current = $result->current();
        $this->assertEquals('<p>This is a test</p>
        <p>This is another block</p>', $current->getContent());
    }
}
