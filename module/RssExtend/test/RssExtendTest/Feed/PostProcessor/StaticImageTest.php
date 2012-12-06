<?php
namespace RssExtendTest\Feed\PostProcessor;

use RssExtend\Feed\PostProcessor\StaticImage;
use RssExtend\Feed\Feed;

class StaticImageTest extends \PHPUnit_Framework_TestCase
{

    public function testProcess ()
    {
        $feed = new Feed;
        $image = new StaticImage('http://localhost', $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<img src="/relative/image.jpg" />');

        $image->process($entry);

        $this->assertEquals('<img src="http://localhost/relative/image.jpg"/>', $entry->getContent());
    }

}
