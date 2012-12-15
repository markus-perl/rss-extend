<?php
namespace RssExtend\Feed\Writer\Extension\Media\Renderer;

use DOMDocument;
use DOMElement;
use Zend\Feed\Writer\Extension;

class Entry extends Extension\AbstractRenderer
{

    /**
     * Render entry
     *
     * @return void
     */
    public function render ()
    {
        $this->setImage($this->dom, $this->base);
    }

    /**
     * Append namespaces to entry
     *
     * @return void
     */
    protected function _appendNamespaces ()
    {
        $this->getRootElement()->setAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
    }


    /**
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function setImage (DOMDocument $dom, DOMElement $root)
    {
        $thumbnail = $this->getDataContainer()->getMediaThumbnail();
        if ($thumbnail) {
            $thumb = $this->dom->createElement('media:thumbnail');

            foreach ($thumbnail as $attribute => $value) {
                $thumb->setAttribute($attribute, $value);
            }

            $root->appendChild($thumb);
        }
    }
}
