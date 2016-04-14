<?php
namespace RssExtend\Feed\Parser;

require_once __DIR__ . '/../../../../../../vendor/urlToAbsolute/url_to_absolute.php';

class Dom extends AbstractParser
{

    /**
     * (non-PHPdoc)
     * @see RssExtend_Worker_Abstract::_getContent()
     */
    protected function getContent(\Zend\Feed\Writer\Entry $entry, $index = null)
    {
        $url = $this->getUrl($entry);
        $html = null;

        if (false === $this->getDownloader()->isLocalFile($url)) {
            $html = $this->getDownloader()->download($url);
        }

        if (!$html) {
            return 'No content found. HTML download failed.';
        }

        $html = $this->removeScriptTag($html);

        $dom = new \RssExtend\Dom\Query();
        $dom->setDocument($html, 'utf-8');

        if (null == $this->config->content) {
            throw new Exception\RuntimeException('config content not set');
        }

        if ($this->config->xhtml) {
            preg_match("/<body.*\<\/body>/s", $html, $body);
            if (isset($body[0])) {
                $dom->loadHtmlFragment($body[0]);
            } else {
                return 'No content found. HTML fragment loading failed';
            }
        }

        $results = $dom->execute(trim($this->config->content));

        $content = '';

        /* @var $node = DomElement */
        foreach ($results as $node) {
            $content .= '<p>' . trim($dom->getInnerHtml($node)) . '</p>';
        }

        if ($content == '') {
            $content = 'No content found. Content empty. Selector: ' . $this->config->content;
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

            $html = $this->removeScriptTag($html);

            $dom = new \RssExtend\Dom\Query();
            $dom->setDocument($html, 'utf-8');

            $results = $dom->execute(trim($this->config->image));

            if (count($results)) {

                $imageUrl = null;
                foreach (array('data-lazy-src', 'data-original', 'src', 'data-cfsrc') as $attribute) {
                    $imageUrl = $results->current()->getAttribute($attribute);

                    if ($imageUrl) {
                        break;
                    }
                }

                if ($imageUrl) {

                    if (substr($imageUrl, 0, 7) == 'http://' || substr($imageUrl, 0, 8) == 'https://') {
                        return $imageUrl;
                    } elseif (substr($imageUrl, 0, 2) == '//') {
                        return 'http:' . $imageUrl;
                    } else {
                        return url_to_absolute($entry->getLink(), $imageUrl);
                    }

                }
            }
        }

        return null;
    }

    protected function removeScriptTag($html)
    {
        if ($this->config->removeScriptTags) {
            $html = preg_replace('/' .
                preg_quote('<script', '/') .
                '[\s\S]+?' .
                preg_quote('/script>', '/') .
                '/', '', $html);
        }
        return $html;
    }

}