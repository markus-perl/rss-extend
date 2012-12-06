<?php
namespace RssExtendTest\Feed\PostProcessor;

use RssExtend\Feed\PostProcessor\Mobilizer;
use RssExtend\Feed\Feed;

class MobilizerTest extends \PHPUnit_Framework_TestCase
{

    public function testProcess ()
    {
        $feed = new Feed;
        $image = new Mobilizer(null, $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<a href="http://localhost"');

        $image->process($entry);

        $this->assertEquals('<a href="http://localhost"/> <a href="http://www.google.com/gwt/n?u=http%3A%2F%2Flocalhost&amp;noimg=1">(mobilizer)</a>', $entry->getContent());
    }

}
