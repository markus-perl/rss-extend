<?php
namespace RssExtend\Feed\Parser;

use \Zend\Validator\Uri;

require_once __DIR__ . '/../../../../../../vendor/urlToAbsolute/url_to_absolute.php';

class Dom extends AbstractParser
{

    /**
     * @param \Zend\Feed\Reader\Entry\EntryInterface $entry
     * @return string
     */
    private function getUrl (\Zend\Feed\Writer\Entry $entry)
    {
        $url = $entry->getContent();
        $validator = new \Zend\Uri\Http();
        if ($url === null || $validator->isValid($url) === false) {
            $url = $entry->getLink();
        }

        return $url;
    }

    /**
     * (non-PHPdoc)
     * @see RssExtend_Worker_Abstract::_getContent()
     */
    protected function getContent (\Zend\Feed\Writer\Entry $entry)
    {
        $url = $this->getUrl($entry);

        $url = $this->getUrl($entry);
        $html = $this->getDownloader()->download($url);

        $noContent = 'no content found';

        if (!$html) {
            return $noContent;
        }

        $dom = new \Zend\Dom\Query();
        $dom->setDocument($html, 'utf-8');

        if (null == $this->config->content) {
            throw new Exception\RuntimeException('config content not set');
        }

        if ($this->config->xhtml)
        {
            preg_match("/<body.*\<\/body>/s", $html, $body);
            $content = '<?xml version="1.0" encoding="UTF-8" ?>
                <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml">
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head><META http-equiv=Content-Type content="text/html; charset=UTF-8"></head>
                ' . $body[0] . '</html>';
            $dom->setDocumentXhtml( $content, 'utf-8' );
        }

        $results = $dom->execute(trim($this->config->content));

        $content = '';

        /* @var $node = DomElement */
        foreach ($results as $node) {
            $content .= '<p>' . trim($this->getInnerHtml($node)) . '</p>';
        }

        if ($content == '') {
            $content = $noContent;
        }

        return $content;
    }

    /**
     * return the inner HTML of a DomElement
     *
     * @param DomElement $node
     * @return string
     */
    private function getInnerHtml ($node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }


    /**
     * @return string
     */
    protected function getImage (\Zend\Feed\Writer\Entry $entry)
    {

        if (isset($this->config->image)) {
            $url = $this->getUrl($entry);
            $html = $this->getDownloader()->download($url);

            $dom = new \Zend\Dom\Query();
            $dom->setDocument($html, 'utf-8');

            $results = $dom->execute(trim($this->config->image));

            if (count($results)) {
                $imageUrl = $results->current()->getAttribute('src');

                if ($imageUrl) {

                    if (substr($imageUrl, 0, 7) == 'http://' || substr($imageUrl, 0, 8) == 'https://')
                    {
                        return $imageUrl;
                    }
                    else
                    {
                        return url_to_absolute($entry->getLink(), $imageUrl);
                    }

                }
            }
        }

        return null;
    }
}