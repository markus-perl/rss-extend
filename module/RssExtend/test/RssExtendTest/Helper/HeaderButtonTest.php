<?php
namespace RssExtendTest\Feed;

use RssExtend\Helper\HeaderButton;

class HeaderButtonTest extends \PHPUnit_Framework_TestCase
{


    public function testHeaderButton ()
    {
        $button = new HeaderButton;

        $result = $button('test', 'http://localhost', 'arrow-l', HeaderButton::LEFT);
        $this->assertEquals('<a class="ui-btn-left" href="http://localhost"  data-icon="arrow-l">test</a>', $result);
    }
}
