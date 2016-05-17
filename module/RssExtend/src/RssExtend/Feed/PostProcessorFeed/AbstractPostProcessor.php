<?php
namespace RssExtend\Feed\PostProcessorFeed;
use RssExtend\Feed\Processor\AbstractProcessor;
use \Zend\Feed\Writer\Entry;


abstract class AbstractPostProcessor extends AbstractProcessor
{

    /**
     * @param \Zend\Feed\Writer\Feed $feed
     * @return \Zend\Feed\Writer\Feed
     */
    abstract public function process (\Zend\Feed\Writer\Feed $feed);
}