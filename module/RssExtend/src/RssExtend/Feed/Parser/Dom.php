<?php
namespace RssExtend\Feed\Parser;

use Zend\Validator\Uri;

require_once __DIR__ . '/../../../../../../vendor/urlToAbsolute/url_to_absolute.php';

class Dom extends AbstractParser
{

    /**
     * @param \Zend\Feed\Reader\Entry\EntryInterface $entry
     * @return string
     */
    private function getUrl(\Zend\Feed\Writer\Entry $entry)
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
    protected function getContent(\Zend\Feed\Writer\Entry $entry)
    {
        $url = $this->getUrl($entry);

        $url = $this->getUrl($entry);
        $html = $this->getDownloader()->download($url);

        $noContent = 'no content found';

        if (!$html) {
            return $noContent;
        }

        $dom = new \RssExtend\Dom\Query();
        $dom->setDocument($html, 'utf-8');

        if (null == $this->config->content) {
            throw new Exception\RuntimeException('config content not set');
        }

        if ($this->config->xhtml) {
            preg_match("/<body.*\<\/body>/s", $html, $body);
            $dom->loadHtmlFragment($body[0]);
        }

        $results = $dom->execute(trim($this->config->content));

        $content = '';

        /* @var $node = DomElement */
        foreach ($results as $node) {
            $content .= '<p>' . trim($dom->getInnerHtml($node)) . '</p>';
        }

        if ($content == '') {
            $content = $noContent;
        }

        return $content;
    }

    /**
     * @return string
     */
    protected function getImage(\Zend\Feed\Writer\Entry $entry)
    {

        if (isset($this->config->image)) {
            $url = $this->getUrl($entry);
            $html = $this->getDownloader()->download($url);

            $dom = new \RssExtend\Dom\Query();
            $dom->setDocument($html, 'utf-8');

            $results = $dom->execute(trim($this->config->image));

            if (count($results)) {
                $imageUrl = $results->current()->getAttribute('src');

                if ($imageUrl) {

                    if (substr($imageUrl, 0, 7) == 'http://' || substr($imageUrl, 0, 8) == 'https://') {
                        return $imageUrl;
                    } else {
                        return url_to_absolute($entry->getLink(), $imageUrl);
                    }

                }
            }
        }

        return null;
    }
}