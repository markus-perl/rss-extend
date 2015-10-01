<?php
namespace RssExtend\Feed\Parser;

use \Zend\Validator\Uri;

class None extends AbstractParser
{

    protected function getContent(\Zend\Feed\Writer\Entry $entry, $index = null)
    {
        return $entry->getContent();
    }

}