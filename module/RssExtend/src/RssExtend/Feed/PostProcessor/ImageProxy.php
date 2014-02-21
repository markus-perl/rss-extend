<?php
namespace RssExtend\Feed\PostProcessor;
use RssExtend\Feed\Image;
use \Zend\Feed\Writer\Entry;

class ImageProxy extends AbstractPostProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process (Entry $entry)
    {
        $dom = $this->getDom($entry->getContent());
        $res = $dom->execute('img');

        $imageHelper = new Image();

        /*
         * @var $element DOMElement
         */
        foreach ($res as $element) {
            $src = $element->getAttribute('src');
            $element->setAttribute('src', $imageHelper->url($src));
        }

        $entry->setContent($this->extractBody($res));

        if ($entry->getMediaThumbnail()) {
            $thumb = $entry->getMediaThumbnail();
            $thumb['url'] = $imageHelper->url($thumb['url']);
            $entry->setMediaThumbnail($thumb);
        }

        return $entry;
    }

}