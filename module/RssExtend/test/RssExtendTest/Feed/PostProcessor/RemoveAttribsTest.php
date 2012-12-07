<?php
namespace RssExtendTest\Feed\PostProcessor;

use RssExtend\Feed\PostProcessor\RemoveAttribs;
use RssExtend\Feed\Feed;

class RemoveAttribsTest extends \PHPUnit_Framework_TestCase
{

    public function testProcessImage ()
    {
        $feed = new Feed;
        $image = new RemoveAttribs('', $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<img style="font-family:Arial" src="http://localhost/image.jpg"/>');

        $image->process($entry);

        $this->assertEquals('<img src="http://localhost/image.jpg"/>', $entry->getContent());
    }

    public function testProcessAnchor ()
    {
        $feed = new Feed;
        $image = new RemoveAttribs('', $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<a style="font-family:Arial" href="http://localhost">link</a>');

        $image->process($entry);

        $this->assertEquals('<a href="http://localhost">link</a>', $entry->getContent());
    }

    public function testProcessAnchor_2 ()
    {
        $feed = new Feed;
        $image = new RemoveAttribs('', $feed);

        $entry = new \Zend\Feed\Writer\Entry();
        $entry->setContent('<a style="font-family:Arial" align="right" href="http://localhost">link</a>');

        $image->process($entry);

        $this->assertEquals('<a href="http://localhost">link</a>', $entry->getContent());
    }



}
