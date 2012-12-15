<?php
namespace RssExtendTest\Feed;

use RssExtend\Feed\Writer\Extension\Media\Entry;

class EntryTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetEncoding ()
    {
        $entry = new Entry;
        $entry->setEncoding($encoding = 'ISO8859-1');
        $this->assertEquals($encoding, $entry->getEncoding());
    }

    public function testSetGetMediaThumbnail ()
    {
        $entry = new Entry;
        $entry->setMediaThumbnail($url = 'http://localhost/image.jpg', 100, 100);
        $this->assertEquals(array(
                                 'url' => $url,
                                 'width' => 100,
                                 'height' => 100
                            ), $entry->getMediaThumbnail());
    }


}
