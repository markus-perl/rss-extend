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
        $entry->setContent('<a href="http://localhost">test</a>');

        $image->process($entry);

        $this->assertEquals('<a href="http://localhost">test</a> <a href="http://www.google.com/gwt/n?u=http%3A%2F%2Flocalhost&amp;noimg=1">(mobilizer)</a>', $entry->getContent());
    }


    public function testAnchor ()
    {
        $feed = new Feed;
        $image = new Mobilizer(null, $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<a href="#">test</a>');

        $image->process($entry);

        $this->assertEquals('<a href="#">test</a>', $entry->getContent());
    }

    public function testJavascript ()
    {
        $feed = new Feed;
        $image = new Mobilizer(null, $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<a href="javascript:void(0);">test</a>');

        $image->process($entry);

        $this->assertEquals('<a href="javascript:void(0);">test</a>', $entry->getContent());
    }

    public function testMailto ()
    {
        $feed = new Feed;
        $image = new Mobilizer(null, $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<a href="mailto:m@localhost">test</a>');

        $image->process($entry);

        $this->assertEquals('<a href="mailto:m@localhost">test</a>', $entry->getContent());
    }


}
