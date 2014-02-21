<?php
namespace RssExtend\Feed\PostProcessor;

use RssExtend\Feed\Image;
use RssExtend\Feed\PostProcessor\AbstractPostProcessor;
use RssExtend\Feed\PreProcessor\AbstractPreProcessor;
use \Zend\Feed\Writer\Entry;

class PageSpeedFix extends AbstractPostProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process(Entry $entry)
    {
        $dom = $this->getDom($entry->getContent());
        $res = $dom->execute('img');

        /*
         * @var $element DOMElement
         */
        foreach ($res as $element) {
            $src = $element->getAttribute('pagespeed_lazy_src');

            if ($src) {
                $element->setAttribute('src', $src);
            }
        }

        $entry->setContent($this->extractBody($res));

        return $entry;
    }

}