<?php
namespace RssExtend\Feed\PostProcessor;
use \Zend\Dom\Query;
use \Zend\Config\Config;
use \Zend\Feed\Writer\Entry;

abstract class AbstractPostProcessor
{
    /**
     * @var \RssExtend\Feed\Feed
     */
    protected $feed;

    /**
     * @var \Zend\Config\Config
     */
    protected $config;

    /**
     * @param Entry $entry
     * @return Entry
     */
    abstract public function process (Entry $entry);

    /**
     * @param string $content
     * @return Query
     */
    protected function getDom ($content)
    {
        $dom = new Query($content);
        $content = str_replace(']]>', '', $content);

        $content = '<?xml version="1.0" encoding="UTF-8" ?>
		    			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		    			<html xmlns="http://www.w3.org/1999/xhtml">
		    			<head><META http-equiv=Content-Type content="text/html; charset=UTF-8"></head>
		    			<body id="body">' . $content . '</body></html>';

        $dom->setDocumentXhtml($content, 'utf-8');
        $res = $dom->execute('body');

        return $dom;
    }

    /**
     * @param \RssExtend\Feed\Feed $feed
     * @param \Zend\Config\Config|string $config
     */
    public function __construct ($config = null, \RssExtend\Feed\Feed $feed)
    {
        $this->config = new \Zend\Config\Config(array());
        if ($config) {
            $this->config = $config;
        }

        $this->feed = $feed;
    }


    /**
     * return the inner HTML of a DomElement
     *
     * @param DomElement $node
     * @return string
     */
    protected function extractBody (\Zend\Dom\NodeList $res)
    {
        $domDocument = $res->getDocument();
        $xml = $domDocument->saveXML($domDocument->documentElement);

        $innerHTML = '';

        $bodyDoc = new Query($xml);
        $node = $bodyDoc->execute('body')->current();

        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }
}