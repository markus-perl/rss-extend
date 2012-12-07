<?php
namespace RssExtend\Feed\PostProcessor;
use RssExtend\Feed\PostProcessor\AbstractPostProcessor;
use \Zend\Feed\Writer\Entry;
use \Zend\Dom\Query;

class Mobilizer extends AbstractPostProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process (Entry $entry)
    {
        $dom = $this->getDom($entry->getContent());
        $res = $dom->execute('a');

        $domDocument = $res->getDocument();

        /*
         * @var $element DOMElement
         */
        foreach ($res as $element) {

            $href = $element->getAttribute('href');
            $url = urlencode($href);

            if (mb_strlen($url) > 7 && substr_count($href, 'javascript:') == 0 && substr_count($href, 'mailto:') == 0) {

                $url = 'http://www.google.com/gwt/n?u=' . $url . '&noimg=1';

                $mobilizer = $domDocument->createElement('a', '(mobilizer)');
                $mobilizer->setAttribute('href', $url);

                $element->parentNode->insertBefore($mobilizer, $element->nextSibling);
                $element->parentNode->insertBefore(new \DOMText (' '), $element->nextSibling);
            }
        }

        $entry->setContent($this->extractBody($res));
        return $entry;
    }

}