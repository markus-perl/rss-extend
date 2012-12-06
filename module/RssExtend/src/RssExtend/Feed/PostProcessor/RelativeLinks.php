<?php
namespace RssExtend\Feed\PostProcessor;
use RssExtend\Feed\PostProcessor\AbstractPostProcessor;
use \Zend\Feed\Writer\Entry;
use \Zend\Dom\Query;

class RelativeLinks extends AbstractPostProcessor
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

        $url = $this->config;

        if (false == is_string($url) || mb_strlen($url) < 1) {
            throw new Exception\RuntimeException('domain not set');
        }

        /*
         * @var $element DOMElement
         */
        foreach ($res as $element) {
            $href = $element->getAttribute('href');

            if (!substr_count($href, 'http')) {
                $element->setAttribute('href', $url . $href);
            }
        }

        $entry->setContent($this->extractBody($res));
        return $entry;
    }

}