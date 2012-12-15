<?php
namespace RssExtend\Feed\PostProcessor;
use RssExtend\Feed\PostProcessor\AbstractPostProcessor;
use \Zend\Feed\Writer\Entry;
use \Zend\Dom\Query;

class StaticImage extends AbstractPostProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process (Entry $entry)
    {
        $dom = $this->getDom($entry->getContent());
        $res = $dom->execute('img');

        /*
         * @var $element DOMElement
         */
        foreach ($res as $element) {
            $src = $element->getAttribute('src');
            $element->setAttribute('src', $this->config . $src);
        }

        $entry->setContent($this->extractBody($res));

        if ($entry->getMediaThumbnail()) {
            $thumb = $entry->getMediaThumbnail();
            $thumb['url'] = $this->config . $thumb['url'];
            $entry->setMediaThumbnail($thumb);
        }

        return $entry;
    }

}