<?php
namespace RssExtend\Feed\PreProcessor;

use RssExtend\Feed\PreProcessor\AbstractPreProcessor;
use \Zend\Dom\Query;
use \Zend\Feed\Writer\Entry;

class DateCreated extends AbstractPreProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process(Entry $entry)
    {
        $entry->setDateModified($entry->getDateCreated());
        return $entry;
    }

}