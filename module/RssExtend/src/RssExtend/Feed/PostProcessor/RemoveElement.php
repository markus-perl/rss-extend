<?php
namespace RssExtend\Feed\PostProcessor;

use Zend\Feed\Writer\Entry;

class RemoveElement extends AbstractPostProcessor
{


    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process(Entry $entry)
    {
        $dom = $this->getDom($entry->getContent());
        $res = $dom->execute($this->config);

        /*
      * @var $element DOMElement
      */
        foreach ($res as $element) {
            if ($element->hasAttribute('class') && $element->getAttribute('class') == 'rssextend') {
                continue;
            }
            $element->parentNode->removeChild($element);
        }

        $entry->setContent($this->extractBody($res));

        return $entry;
    }

}