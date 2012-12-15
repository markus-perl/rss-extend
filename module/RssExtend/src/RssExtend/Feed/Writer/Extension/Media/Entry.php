<?php

namespace RssExtend\Feed\Writer\Extension\Media;

use Zend\Feed\Writer;
use Zend\Uri;

class Entry
{
    /**
     * Array of Feed data for rendering by Extension's renderers
     *
     * @var array
     */
    protected $data = array();

    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Set feed encoding
     *
     * @param  string $enc
     * @return Feed
     */
    public function setEncoding ($enc)
    {
        $this->encoding = $enc;
        return $this;
    }

    /**
     * Get feed encoding
     *
     * @return string
     */
    public function getEncoding ()
    {
        return $this->encoding;
    }

    /**
     * @param string|array $url
     * @param int $width
     * @param int $height
     */
    public function setMediaThumbnail ($url, $width = null, $height = null)
    {
        if (is_array($url)) {
            $this->data['thumbnail'] = $url;
            return;
        }

        $this->data['thumbnail'] = array(
            'url' => $url,
            'width' => $width,
            'height' => $height
        );
    }

    /**
     * Overloading to itunes specific setters
     *
     * @param  string $method
     * @param  array $params
     * @throws Writer\Exception\BadMethodCallException
     * @return mixed
     */
    public function __call ($method, array $params)
    {
        $point = lcfirst(substr($method, 8));
        if (!array_key_exists($point, $this->data) || empty($this->data[$point])
        ) {
            return null;
        }
        return $this->data[$point];
    }

}
