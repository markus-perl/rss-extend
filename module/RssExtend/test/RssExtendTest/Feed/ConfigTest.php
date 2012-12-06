<?php
namespace RssExtendTest\Feed;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct ()
    {
        $config = new \RssExtend\Feed\Config(__DIR__ . '/testData');

        $this->assertEquals(array(
                                 'feed1' => array(
                                     'name' => 'Feed 1',
                                     'url' => 'http://localhost/feed1',
                                     'method' => 'dom',
                                     'postProcess' => array('staticImage' => 'http://localhost')
                                 ),
                                 'feed2' => array(
                                     'name' => 'Feed 2',
                                     'url' => 'http://localhost/feed2',
                                     'method' => 'trim',
                                 ),
                            ), $config->toArray());
    }


    /**
     * @expectedException \RssExtend\Feed\Exception\RuntimeException
     */
    public function testConstructInvalidDirectory ()
    {
        $config = new \RssExtend\Feed\Config(__DIR__ . '/notADirectory');
    }
}
