<?php
namespace RssExtendTest\Feed\PostProcessor;

use RssExtend\Feed\PostProcessor\RelativeLinks;
use RssExtend\Feed\Feed;

class RelativeLinksTest extends \PHPUnit_Framework_TestCase
{

    public function testProcess ()
    {
        $feed = new Feed;
        $image = new RelativeLinks('http://localhost', $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<a href="/my/url"');

        $image->process($entry);

        $this->assertEquals('<a href="http://localhost/my/url"/>', $entry->getContent());
    }

}
