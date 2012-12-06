<?php
namespace RssExtend\Feed\Parser;

use \Zend\Validator\Uri;

class None extends AbstractParser
{

    protected function getContent (\Zend\Feed\Reader\Entry\EntryInterface $entry)
    {
        return $entry->getContent();
    }

}