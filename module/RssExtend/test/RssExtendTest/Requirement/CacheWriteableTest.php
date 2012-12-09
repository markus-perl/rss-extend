<?php
namespace RssExtendTest\Requirement;

use RssExtend\Requirement\CacheWriteable;

class CacheWriteableTest extends \PHPUnit_Framework_TestCase
{


    public function testCheckRequirementInvalidDir ()
    {
        $check = new CacheWriteable('/tmp/notADir');

        $this->assertFalse($check->checkRequirement());
    }

    public function testCheckRequirementValidDir ()
    {
        $check = new CacheWriteable('/tmp');

        $this->assertTrue($check->checkRequirement());
    }
}
