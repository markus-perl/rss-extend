<?php
namespace RssExtend\Feed\PostProcessor;

use Zend\Feed\Writer\Entry;

class ReadabilityInline extends AbstractPostProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process(Entry $entry)
    {
        $dom = $this->getDom($entry->getContent());
        $res = $dom->execute('a');
        $inlineContent = '';

        $domDocument = $res->getDocument();

        /*
         * @var $element DOMElement
         */
        foreach ($res as $element) {

            $href = $element->getAttribute('href');
            $url = urlencode($href);

            if (mb_strlen($url) > 7 && substr_count($href, 'javascript:') == 0 && substr_count($href, 'mailto:') == 0) {

                $token = $this->config->token;

                if (!$token) {
                    throw new Exception\RuntimeException('token not set');
                }

                $readabilityUrl = 'http://www.readability.com/api/content/v1/parser?url=' . $url . '&token=' . $token;
                $json = json_decode($this->feed->getParser()->getDownloader()->download($readabilityUrl));

                if ($json && isset($json->content, $json->title)) {

                    $removeAttribs = new RemoveAttribs($this->config, $this->feed);
                    $content = $removeAttribs->remove($json->content);
                    $content = strip_tags($content, '<p><br><a><img>');

                    $id = substr($href, 0, 30);
                    if (strlen($id) < strlen($href)) {
                        $id .= '...';
                    }

                    $start = '<br /><hr /><div style="text-align: center;">INLINE CONTENT: ' . $json->title . '</div><br />';
                    $end = '<br /><hr />';

                    $inlineContent .= $start . $content . $end;

                    $marker = $domDocument->createElement('span', '(Inline Content ' . $id . ')');
                    $element->parentNode->insertBefore($marker, $element->nextSibling);
                    $element->parentNode->insertBefore(new \DOMText (' '), $element->nextSibling);
                }
            }
        }

        $entry->setContent($this->extractBody($res) . $inlineContent);
        return $entry;
    }

}