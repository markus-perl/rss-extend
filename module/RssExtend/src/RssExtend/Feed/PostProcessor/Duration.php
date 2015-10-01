<?php
namespace RssExtend\Feed\PostProcessor;

use RssExtend\Feed\PreProcessor\AbstractPreProcessor;
use \Zend\Feed\Writer\Entry;

class Duration extends AbstractPreProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process(Entry $entry)
    {
        if ($entry->getItunesDuration() && $entry->getItunesDuration() < $this->config) {
            return null;
        }

        return $entry;
    }

}