<?php
namespace RssExtend\Feed\PostProcessor;
use RssExtend\Feed\PostProcessor\AbstractPostProcessor;
use \Zend\Feed\Writer\Entry;
use \Zend\Dom\Query;

class RemoveAttribs extends AbstractPostProcessor
{

    /**
     * @param Entry $entry
     * @return Entry
     */
    public function process (Entry $entry)
    {
        $content = $entry->getContent();
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
                                                          'href'
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

        $entry->setContent($content);
        return $entry;
    }

}