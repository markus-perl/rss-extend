<?php
namespace RssExtendTest\Requirement;

use RssExtend\Requirement\PhpVersion;

class PhpVersionTest extends \PHPUnit_Framework_TestCase
{

    public function testCheckRequirement5315 ()
    {
        $check = new PhpVersion;
        $check->setVersion('5.3.15');

        $this->assertTrue($check->checkRequirement());
    }

    public function testCheckRequirement5316 ()
    {
        $check = new PhpVersion;
        $check->setVersion('5.3.16');

        $this->assertTrue($check->checkRequirement());
    }

    public function testCheckRequirementToOld ()
    {
        $check = new PhpVersion;
        $check->setVersion('5.3.14');

        $this->assertFalse($check->checkRequirement());
    }

    public function testCheckRequirementPhp5410 ()
    {
        $check = new PhpVersion;
        $check->setVersion('5.4.10');

        $this->assertTrue($check->checkRequirement());
    }
}
