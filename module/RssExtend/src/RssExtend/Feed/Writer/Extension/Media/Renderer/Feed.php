<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Feed
 */

namespace RssExtend\Feed\Writer\Extension\Media\Renderer;

use DOMDocument;
use DOMElement;
use Zend\Feed\Writer\Extension;

/**
* @category Zend
* @package Zend_Feed_Writer
*/
class Feed extends Extension\AbstractRenderer
{

    /**
     * Set to TRUE if a rendering method actually renders something. This
     * is used to prevent premature appending of a XML namespace declaration
     * until an element which requires it is actually appended.
     *
     * @var bool
     */
    protected $called = false;

    /**
     * Render feed
     *
     * @return void
     */
    public function render()
    {
            $this->_appendNamespaces();
    }

    /**
     * Append namespaces to feed element
     *
     * @return void
     */
    protected function _appendNamespaces()
    {
        $this->getRootElement()->setAttribute('xmlns:media',
            'http://search.yahoo.com/mrss/');
    }


}
