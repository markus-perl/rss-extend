<?php
namespace RssExtendTest\Feed\PostProcessor;

use RssExtend\Feed\PostProcessor\Summary;
use RssExtend\Feed\Feed;

class SummeryTest extends \PHPUnit_Framework_TestCase
{

    public function testProcess ()
    {
        $feed = new Feed;
        $mobilizer = new Summary(null, $feed);

        $entry = new \Zend\Feed\Writer\Entry();

        $content = 'Das ist ein langer Satz, der eventuell gekürzt werden könnte und dann auch noch Sinn ergibt. Beim Kürzen wird er Text nach bestimmten Stopwords durchsucht.
        Wird ein solches Wort gesucht wird dahingehend eine logische Trennung vorgenommen und versucht, weniger wichtige Satzteile, wegzulassen.
        Wie gut das funktioniert wird dieser Test hier zeigen';

        $expected = '<ul><li>ist ein langer Satz der eventuell gekürzt werden könnte und dann auch noch Sinn ergibt</li><li>wird dahingehend eine logische Trennung vorgenommen und versucht weniger wichtige Satzteile wegzulassen</li><li>Summary: 365 -> 191</li></ul>';

        $entry->setContent($content);
        $entry->setContentPlain($content);

        $mobilizer->process($entry);

        $this->assertEquals($expected, $entry->getContent());
    }

}
