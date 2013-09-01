<?php

namespace RssExtend\Dom;

use Zend\Dom\Query as ZendDomQuery;

class Query extends ZendDomQuery
{

    public function loadHtmlFragment($html)
    {
        if ($html instanceof \DOMElement) {
            $html = $this->getInnerHtml($html);
        }

        $content = '<?xml version="1.0" encoding="UTF-8" ?>
                <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml">
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head><META http-equiv=Content-Type content="text/html; charset=UTF-8"></head>
                ' . $html . '</html>';

        return $this->setDocumentXhtml($content, 'utf-8');
    }

    /**
     * return the inner HTML of a DomElement
     *
     * @param DomElement $node
     * @return string
     */
    public function getInnerHtml($node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }

}