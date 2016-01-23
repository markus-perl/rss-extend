<?php
namespace RssExtend\Feed\PostProcessor;

use Zend\Feed\Writer\Entry;

class RemoveAttribs extends AbstractPostProcessor
{

    public function remove($content)
    {
        foreach (array(
                     'img',
                     'br',
                     'a',
                     'div',
                     'ul',
                     'li',
                     'span'
                 ) as $tag) {

            $dom = $this->getDom($content);
            $res = $dom->execute($tag);

            /* @var $element DOMElement */
            foreach ($res as $element) {

                $attribsToRemove = array();
                foreach ($element->attributes as $attrName => $attrNode) {
                    if (false == in_array($attrName, array(
                            'src',
                            'href',
                            'alt',
                        ))
                    ) {
                        $attribsToRemove[] = $attrName;
                    }
                }

                foreach ($attribsToRemove as $attrName) {
                    $element->removeAttribute($attrName);
                }

            }
            $content = $this->extractBody($res);
        }
        return $content;
    }

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process(Entry $entry)
    {
        $entry->setContent($this->remove($entry->getContent()));
        return $entry;
    }

}