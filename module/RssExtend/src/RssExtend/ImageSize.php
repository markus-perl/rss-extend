<?php
namespace RssExtend;


class ImageSize
{

    /**
     * @var \Zend\Cache\Storage\Adapter\Filesystem
     */
    private $cache = null;

    /**
     * @var Downloader
     */
    private $downloader = null;

    /**
     * @param \Zend\Cache\Storage\Adapter\Filesystem $cache
     */
    public function setCache (\Zend\Cache\Storage\Adapter\Filesystem $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    public function getCache ()
    {
        return $this->cache;
    }

    /**
     * @param Downloader $downloader
     */
    public function setDownloader ($downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @return Downloader
     */
    public function getDownloader ()
    {
        if (null === $this->downloader) {
            $this->setDownloader(new Downloader());
        }

        return $this->downloader;
    }

    /**
     * @param string $url
     * @return array
     */
    public function getSizeByUrl ($url)
    {
        $size = null;

        if ($this->getCache()) {
            $size = $this->getCache()->getItem($key = 'imgSize' . crc32($url));
        }

        if ($size) {
            $size = explode(':', $size);
            return array(
                'x' => (int) $size[0],
                'y' => (int) $size[1],
            );
        }

        $tmpFile = tempnam(__DIR__ . '/../../../../tmp', 'image');

        $size = array(
            'x' => 100,
            'y' => 100
        );

        if ($this->getDownloader()->download($url, false, $tmpFile)) {
            if (file_exists($tmpFile)) {
                $imageSize = getimagesize($tmpFile);
                unlink($tmpFile);
            }

            if ($imageSize) {

                $size = array(
                    'x' => $imageSize[0],
                    'y' => $imageSize[1]
                );

                if ($this->getCache()) {
                    $this->getCache()->setItem($key, $size['x'] . ':' . $size['y']);
                }
            }
        }

        return $size;

    }
}