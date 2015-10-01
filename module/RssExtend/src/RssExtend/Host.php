<?php
namespace RssExtend;

use RssExtend\Exception\RuntimeException;

class Host
{

    /**
     * @var string
     */
    private $_cacheDir = null;

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->_cacheDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->_cacheDir = $cacheDir;
    }

    /**
     * @return string
     */
    public function getDomainWithProtocol()
    {
        if (!$this->getCacheDir()) {
            throw new RuntimeException('cache dir not set');
        }

        $serverUrlFile = realpath($this->getCacheDir()) . '/server-url';

        $host = null;
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = ((!empty($_SERVER['HTTPS']) && mb_strlen($_SERVER['HTTPS']) > 0 && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $protocol . $_SERVER['HTTP_HOST'];
            file_put_contents($serverUrlFile, $host);
        } else {
            if (file_exists($serverUrlFile)) {
                $host = file_get_contents($serverUrlFile);
            }
        }

        return $host;
    }

}