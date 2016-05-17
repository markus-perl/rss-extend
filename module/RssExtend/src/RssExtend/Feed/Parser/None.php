<?php
namespace RssExtend\Feed\Parser;

class None extends AbstractParser
{

    public function getContent(\Zend\Feed\Writer\Entry $entry, $index = null)
    {
        return null;
    }


    public function getUpdatedFeed(\Zend\Feed\Writer\Feed $feed)
    {
        return $feed;
    }


}