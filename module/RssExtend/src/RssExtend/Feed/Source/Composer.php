<?php
namespace RssExtend\Feed\Source;

use RssExtend\Exception;
use RssExtend\Feed\Source\AbstractSource;

require_once __DIR__ . '/../../../../../../vendor/urlToAbsolute/url_to_absolute.php';


class Composer extends AbstractSource
{

    /**
     * @var string
     */
    private $url;

    /**
     * @var \Zend\Config\Config
     */
    private $dom;

    /**
     * @var \RssExtend\Dom\Query
     */
    private $domQuery = null;

    /**
     * @var \Zend\Feed\Writer\Feed
     */
    private $feedOutput = null;

    /**
     * @var array
     */
    private $items = null;

    /**
     * @param \Zend\Config\Config $config
     * @throws Exception\RuntimeException
     */
    public function parseConfig(\Zend\Config\Config $config)
    {
        if (null === $config->url) {
            throw new Exception\RuntimeException('url not set');
        }
        $this->setUrl($config->url);

        if (null === $config->dom) {
            throw new Exception\RuntimeException('dom not set');
        }
        $this->setDom($config->dom);
    }


    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param \RssExtend\Feed\Config $dom
     */
    public function setDom(\RssExtend\Feed\Config $dom)
    {
        foreach ($dom as $element) {
            if (!$element->content) {
                throw new Exception\RuntimeException('Dom content element not set');
            }
        }
        $this->dom = $dom;
    }

    /**
     * @return \RssExtend\Feed\Config
     */
    public function getDom()
    {
        return $this->dom;
    }

    private function getFeedOutput()
    {

        if (null == $this->feedOutput) {
            $feed = new \Zend\Feed\Writer\Feed();

            $title = $this->getUrl();
            $description = $this->getUrl();

            $headTitle = $this->domQuery->queryXpath('//head/title');
            if (count($headTitle) && strlen($headTitle->current()->nodeValue) > 0) {
                $title = $headTitle->current()->nodeValue;
            }

            $metaDescription = $this->domQuery->queryXpath('//meta[@name="description"]');

            if (count($metaDescription) && strlen($metaDescription->current()->getAttribute('content')) > 0) {
                $description = $metaDescription->current()->getAttribute('content');
            }

            $feed = new \Zend\Feed\Writer\Feed();
            $feed->setTitle($title);
            $feed->setDescription($description);
            $feed->setLink($this->getUrl());
            $this->feedOutput = $feed;
        }

        return $this->feedOutput;
    }

    /**
     * @return array
     */
    private function getItems()
    {
        if (null === $this->items) {
            $cacheKey = 'c2' . crc32($this->getUrl());
            $cacheEntry = $this->getCache()->getItem($cacheKey);
            $items = null;

            if ($cacheEntry) {
                $items = unserialize($cacheEntry);
            }

            if (!$items) {
                $items = array();
            }
            $this->items = $items;
        }
        return $items;
    }

    /**
     * @param array $items
     */
    private function cacheItems(array $items)
    {
        $cacheKey = 'c2' . crc32($this->getUrl());
        $this->getCache()->setItem($cacheKey, serialize($items));
        $this->items = $items;
    }

    public function getRss()
    {
        $this->items = null;
        $this->feedOutput = null;
        $this->parseConfig($this->getFeed()->getComposerConfig());
        $html = $this->getDownloader()->download($this->getUrl(), false);

        $dom = new \RssExtend\Dom\Query();
        $dom->setDocument($html, 'utf-8');
        $this->domQuery = $dom;

        $items = $this->getItems();

        foreach ($this->getDom() as $part) {
            $limit = 99;
            $count = 0;

            if ($part->limit) {
                $limit = $part->limit;
            }

            $content = $part->content;
            $a = $part->content;

            if ($content instanceof \RssExtend\Feed\Config) {
                $a = $content->a;
            }

            $results = $dom->execute(trim($a));

            /* @var \DomElement $node */
            foreach ($results as $node) {
                $count++;

                $link = $node->getAttribute('href');

                if (substr($link, 0, 4) != 'http') {
                    $link = url_to_absolute($this->getUrl(), $link);
                }

                $title = $node->getAttribute('title');
                if (!$title) {
                    $title = 'Title';

                    if ($content instanceof \RssExtend\Feed\Config && $content->title) {
                        $domTitle = new \RssExtend\Dom\Query();
                        $domTitle->loadHtmlFragment($node);
                        $result = $domTitle->execute($content->title);

                        if (count($result)) {
                            $domTitle = strip_tags($domTitle->getInnerHtml($result->current()));
                        }
                    } else {
                        $domTitle = strip_tags($dom->getInnerHtml($node));
                    }

                    if ($domTitle) {
                        $title = $domTitle;
                    }
                }

                if ($count > $limit) {
                    break 1;
                }

                $alreadyInList = false;
                foreach ($items as $item) {
                    if ($item['l'] == $link || $item['t'] == $title) {
                        $alreadyInList = true;
                        break 1;
                    }
                }

                if (!$alreadyInList) {
                    array_unshift($items, array(
                                               'l' => $link,
                                               't' => $title
                                          ));
                    $items = array_slice($items, 0, 20);
                    $this->cacheItems($items);
                }
            }
        }

        return $this->export();
    }

    /**
     * @return string
     */
    private function export()
    {
        foreach ($this->items as $item) {
            $entry = $this->getFeedOutput()->createEntry();
            $entry->setDescription('placeholder');
            $entry->setLink($item['l']);
            $entry->setTitle($item['t']);
            $this->getFeedOutput()->addEntry($entry);
        }

        return $this->getFeedOutput()->export('rss');
    }

    public function isConfigAvailable()
    {
        if ($this->getFeed()->getComposerConfig()) {
            return true;
        }
        return false;
    }

}