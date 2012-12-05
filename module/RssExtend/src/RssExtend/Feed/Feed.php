<?php
namespace RssExtend\Feed;

use RssExtend\Feed\Parser\AbstractParser;

class Feed
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $encoding = 'utf-8';

    /**
     * @var AbstractParser
     */
    private $parser;

    /**
     * @var string
     */
    private $method;

    /**
     * @var \Zend\Config\Config
     */
    private $methodConfig;

    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @param string $method
     */
    public function setMethod ($method, \Zend\Config\Config $methodConfig = null)
    {
        $this->method = $method;
        $this->methodConfig = $methodConfig;
    }

    /**
     * @return string
     */
    public function getMethod ()
    {
        return $this->method;
    }

    /**
     * @return \Zend\Config\Config
     */
    public function getMethodConfig ()
    {
        return $this->methodConfig;
    }

    /**
     * @param string $encoding
     */
    public function setEncoding ($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @return string
     */
    public function getEncoding ()
    {
        return $this->encoding;
    }

    /**
     * @param string $name
     */
    public function setName ($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * @param \RssExtend\Feed\Parser\AbstractParser $parser
     */
    public function setParser ($parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return \RssExtend\Feed\Parser\AbstractParser
     */
    public function getParser ()
    {
        if (null === $this->parser) {

            $method = $this->getMethod();
            $parserName = 'RssExtend\\Feed\\Parser\\' . ucfirst(mb_strtolower($method));

            if (false == class_exists($parserName)) {
                throw new Exception\RuntimeException('invalid method specified');
            }

            $parser = new $parserName($this, $this->getMethodConfig());
            $this->setParser($parser);
        }

        return $this->parser;
    }

    /**
     * @param string $url
     */
    public function setUrl ($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl ()
    {
        return $this->url;
    }

    /**
     * @param \Zend\Config\Config $config
     */
    public function __construct ($id = null, \Zend\Config\Config $config = null)
    {
        if ($config) {
            $this->parseConfig($config);
        }

        if ($id) {
            $this->setId($id);
        }
    }

    /**
     * @param \Zend\Config\Config $config
     * @throws Exception\RuntimeException
     */
    public function parseConfig (\Zend\Config\Config $config)
    {
        if (null === $config->name) {
            throw new Exception\RuntimeException('name not set');
        }
        $this->setName($config->name);

        if (null === $config->url) {
            throw new Exception\RuntimeException('url not set');
        }
        $this->setUrl($config->url);

        if (null === $config->method) {
            throw new Exception\RuntimeException('method not set');
        }

        $method = $config->method;
        $this->setMethod($method, $config->$method);
    }

}