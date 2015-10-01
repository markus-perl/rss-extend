<?php
namespace RssExtend\Feed\PreProcessor;

use RssExtend\Feed\PreProcessor\AbstractPreProcessor;
use \Zend\Dom\Query;
use \Zend\Feed\Writer\Entry;

class FilterTitle extends AbstractPreProcessor
{

    private function replaceUrl($url)
    {

        if ($this->config->replace && $this->config->replace instanceof \Zend\Config\Config) {
            foreach ($this->config->replace as $item) {

                if (false == isset($item['search']) || false == isset($item['replaceWith'])) {
                    throw new Exception\RuntimeException('search and replaceWith must be set for replace');
                }

                $url = str_replace($item['search'], $item['replaceWith'], $url);
            }
        }

        return $url;
    }

    public function regex($url)
    {
        if ($this->config->regex) {
            preg_match($this->config->regex, $url, $matches);
            if (isset($matches[1])) {
                $url = $matches[1];
            }
        }

        return $url;
    }

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process(Entry $entry)
    {
        $title = mb_strtolower($this->config);

        if (mb_substr_count(mb_strtolower($entry->getTitle()), $title) == 0) {
            return null;
        } else {
            return $entry;
        }

    }

}