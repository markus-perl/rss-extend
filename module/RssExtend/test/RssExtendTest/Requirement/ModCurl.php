<?php
namespace RssExtendTest\Requirement;

use RssExtend\Requirement\ModCurl;

class ModCurlTest extends \PHPUnit_Framework_TestCase
{

    public function testCheckRequirement()
    {
        $check = new ModCurl;
        $this->assertTrue($check->checkRequirement());
    }

}
