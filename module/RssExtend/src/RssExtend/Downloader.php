<?php
namespace RssExtend;

class Downloader
{

    protected $sessionCache = array();

    /**
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $cache = null;

    /**
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $cache
     */
    public function setCache (\Zend\Cache\Storage\Adapter\AbstractAdapter $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return \Zend\Cache\Storage\StorageInterface
     */
    public function getCache ()
    {
        return $this->cache;
    }


    /**
     * @param string $url
     * @return string
     */
    public function download ($url, $cached = true)
    {
        $key = 'url' . crc32($url);

        if (isset($this->sessionCache[$key])) {
            return $this->sessionCache[$key];
        }

        $content = false;

        if ($this->getCache()) {
            $content = $this->getCache()->getItem($key);
        }

        if (false == $content) {
            $content = file_get_contents($url);

            $this->sessionCache[$key] = $content;
            if ($this->getCache()) {
                $this->getCache()->setItem($key, $content);
            }
        }

        return $content;
    }
}