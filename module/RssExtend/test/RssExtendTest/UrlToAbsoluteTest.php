<?php
namespace RssExtendTest;

class UrlToAbsoluteTest extends \PHPUnit_Framework_TestCase
{

    public function testUrl1()
    {
        $result = url_to_absolute('http://www.mypage.net', 'news/pc/3081074/the_game_awards_2014.html');
        $this->assertEquals('http://www.mypage.net/news/pc/3081074/the_game_awards_2014.html', $result);
    }


}
