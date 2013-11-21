<?php
namespace RssExtend\Feed\Parser;

require_once __DIR__ . '/../../../../../../vendor/urlToAbsolute/url_to_absolute.php';

class Readability extends AbstractParser
{

    /**
     * (non-PHPdoc)
     * @see RssExtend_Worker_Abstract::_getContent()
     */
    protected function getContent(\Zend\Feed\Writer\Entry $entry)
    {
        $url = $this->getUrl($entry);

        $token = $this->config->token;

        if (!$token) {
            throw new Exception\RuntimeException('token not set');
        }

        $url = 'http://www.readability.com/api/content/v1/parser?url=' . $url . '&token=' . $token;

        $json = json_decode($this->getDownloader()->download($url));

        if ($json && $json->content) {
            return $json->content;
        }

        $noContent = 'no content found';
        return $noContent;
    }

    /**
     * @return string
     */
    protected function getImage(\Zend\Feed\Writer\Entry $entry)
    {
        return null;
    }
}