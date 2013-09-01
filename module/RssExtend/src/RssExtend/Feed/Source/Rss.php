<?php
namespace RssExtend\Feed\Source;

class Rss extends AbstractSource
{

    /**
     * @return string
     */
    public function getRss()
    {
        return $this->getDownloader()->download($this->getFeed()->getUrl(), false);
    }

    public function isConfigAvailable()
    {
        if ($this->getFeed()->getUrl()) {
            return true;
        }
        return false;
    }

}
