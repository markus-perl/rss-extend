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
     * @var int 100ms
     */
    protected $sleepMin = 0;

    /**
     * @var int 1 sec
     */
    protected $sleepMax = 0;

    public function setSleep ($min, $max)
    {
        $this->setSleepMin($min);
        $this->setSleepMax($max);
    }

    /**
     * @param int $sleepMin
     */
    public function setSleepMin ($sleepMin)
    {
        $this->sleepMin = (int) $sleepMin;
    }

    /**
     * @return int
     */
    public function getSleepMin ()
    {
        return $this->sleepMin;
    }

    /**
     * @param int $sleepMax
     */
    public function setSleepMax ($sleepMax)
    {
        $this->sleepMax = (int) $sleepMax;
    }

    /**
     * @return int
     */
    public function getSleepMax ()
    {
        return $this->sleepMax;
    }

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

            $timeout = array(
                'http' => array(
                    'timeout' => 5
                )
            );

            $context = stream_context_create($timeout);

            $retries = 0;
            while (false == $content) {
                usleep(mt_rand($this->sleepMin, $this->sleepMax));
                $content = file_get_contents($url, null, $context);
                $retries++;
                if ($retries > 2) {
                    break;
                }
            }


            if ($content) {
                $this->sessionCache[$key] = $content;
                if ($this->getCache()) {
                    $this->getCache()->setItem($key, $content);
                }
            } else {
                throw new Exception\RuntimeException('failed to download url ' . $url);
            }
        }

        return $content;
    }
}