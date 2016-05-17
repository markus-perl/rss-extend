<?php
namespace RssExtend\Feed\PostProcessor;

use RssExtend\Feed\Image;
use Zend\Feed\Writer\Entry;

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
    private function replaceSource($text, Entry $entry)
    {
        $dom = $this->getDom($text);
        $res = $dom->execute('img');

        /*
         * @var $element DOMElement
         */
        foreach ($res as $element) {
            $src = $element->getAttribute('src');

            //absolute image url
            if (mb_substr($src, 0, 1) == '/') {
                $parsedUrl = parse_url($entry->getLink());
                $domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                $src = $domain . $src;
            }

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
        $content = $this->replaceSource($entry->getContent(), $entry);

        if ($content) {
            $entry->setContent($content);
        }

        $description = $this->replaceSource($entry->getDescription(), $entry);

        if ($description) {
            $entry->setDescription($description);
        }

        if ($entry->getMediaThumbnail()) {
            $thumb = $entry->getMediaThumbnail();
            $thumb['url'] = $this->_imageHelper->url($thumb['url']);
            $entry->setMediaThumbnail($thumb);
        }

        return $entry;
    }

}