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

        if ($cached) {
            if ($this->getCache()) {
                $content = $this->getCache()->getItem($key);
            }
        }

        if (false == $content) {

            $retries = 0;
            while (false == $content) {
                usleep(mt_rand($this->sleepMin, $this->sleepMax));

                $downloadMethod = null;
                if (function_exists('curl_init') && substr($url, 0, 4) == 'http') {
                    $content = $this->downloadCurl($url);
                    $downloadMethod = 'curl';
                } else {
                    $content = $this->downloadFileGetContents($url);
                    $downloadMethod = 'file_get_contents';
                }

                $retries++;
                if ($retries > 2) {
                    break;
                }
            }


            if ($content) {
                $this->sessionCache[$key] = $content;
                if ($cached && $this->getCache()) {
                    $this->getCache()->setItem($key, $content);
                }
            } else {
                throw new Exception\DownloadException('failed to download url ' . $url . ' with ' . $downloadMethod);
            }
        }

        return $content;
    }

    /**
     * @param $url
     * @return mixed
     */
    private function downloadCurl ($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    private function downloadFileGetContents ($url)
    {
        $timeout = array(
            'http' => array(
                'timeout' => 5
            )
        );

        $context = stream_context_create($timeout);
        return file_get_contents($url, null, $context);

    }
}