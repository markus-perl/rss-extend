<?php
namespace RssExtend\Feed\PreProcessor;
use RssExtend\Feed\Processor\AbstractProcessor;
use \Zend\Feed\Writer\Entry;


abstract class AbstractPreProcessor extends AbstractProcessor
{
    /**
     * @param Entry $entry
     * @return Entry
     */
    abstract public function process (Entry $entry);
}