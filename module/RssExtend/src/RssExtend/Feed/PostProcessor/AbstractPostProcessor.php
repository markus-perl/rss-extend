<?php
namespace RssExtend\Feed\PostProcessor;
use RssExtend\Feed\Processor\AbstractProcessor;
use \Zend\Feed\Writer\Entry;


abstract class AbstractPostProcessor extends AbstractProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    abstract public function process (Entry $entry);
}