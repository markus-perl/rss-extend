<?php
namespace RssExtend\Feed\PostProcessor;

use RssExtend\Feed\Image;
use \Zend\Feed\Writer\Entry;

class ImageProxy extends AbstractPostProcessor
{

    /**
     * @var \RssExtend\Feed\Image
     */
    private $_imageHelper = null;

    public function __construct()
    {
        $this->_imageHelper = new Image();
    }

    /**
     * Replaces the src attribute with a link to our image proxy
     *
     * @param $text
     * @return string
     */
    private function replaceSource($text)
    {
        $dom = $this->getDom($text);
        $res = $dom->execute('img');

        /*
         * @var $element DOMElement
         */
        foreach ($res as $element) {
            $src = $element->getAttribute('src');
            $element->setAttribute('src', $this->_imageHelper->url($src));
        }

        return $this->extractBody($res);
    }

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process(Entry $entry)
    {
        $entry->setContent($this->replaceSource($entry->getContent()));
        $entry->setDescription($this->replaceSource($entry->getDescription()));

        if ($entry->getMediaThumbnail()) {
            $thumb = $entry->getMediaThumbnail();
            $thumb['url'] = $this->_imageHelper->url($thumb['url']);
            $entry->setMediaThumbnail($thumb);
        }

        return $entry;
    }

}