<?php
namespace RssExtendTest\Feed\PreProcessor;

use RssExtend\Feed\PreProcessor\FixUrl;
use RssExtend\Feed\Feed;

class FixUrlTest extends \PHPUnit_Framework_TestCase
{

    public function testProcess ()
    {
        $config = new \Zend\Config\Config(array(
                                               'replace' => array(
                                                   0 => array(
                                                       'search' => '0A',
                                                       'replaceWith' => '0',
                                                   ),
                                               ),
                                          ));

        $feed = new Feed;
        $fixUrl = new FixUrl($config, $feed);

        $updatedFeed = new \Zend\Feed\Writer\Feed();
        $entry = $updatedFeed->createEntry();
        $entry->setLink('http://localhost/test0A');

        $this->assertEquals('http://localhost/test0', $fixUrl->process($entry)->getLink());
    }


}
