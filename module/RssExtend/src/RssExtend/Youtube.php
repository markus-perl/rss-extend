<?php
namespace RssExtend;


use RssExtend\Exception\RuntimeException;

class Youtube
{

    /**
     * @var string
     */
    private $cacheDir = null;

    private $cache = null;

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function getInfo($url, $type)
    {
        $title = $this->getCache()->getItem($key = 'yttitle_' . crc32($url . $type));

        if (!$title) {
            $video = escapeshellarg($url);
            $title = $this->execute('youtube-dl -q --no-progress ' . $video . ' --' . $type)[0];
            $this->getCache()->setItem($key, $title);
        }

        return $title;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getTitle($url)
    {
        return $this->getInfo($url, 'get-title');
    }

    /**
     * @param string $url
     * @return string
     */
    public function getDescription($url)
    {
        return $this->getInfo($url, 'get-description');
    }

    /**
     * @param string $url
     * @return string
     */
    public function getDuration($url)
    {
        return $this->getInfo($url, 'get-duration');
    }

    /**
     * @param \Zend\Cache\Storage\Adapter\Filesystem $cache
     */
    public function setCache(\Zend\Cache\Storage\Adapter\Filesystem $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    public function getCache()
    {
        return $this->cache;
    }

    public function getCacheFilePath($hash)
    {
        $cacheDir = realpath($this->getCacheDir());
        $cacheFile = $cacheDir . '/' . $hash . '.ytmp';
        return $cacheFile;
    }

    public function download($url, $target, $audioOnly, $videoFormat = 'webm')
    {
        if (!file_exists($target)) {

            $video = escapeshellarg($url);
            $cacheDir = realpath($this->getCacheDir());
            $tmpFile = $path = $cacheDir . '/' . uniqid('youtube-') . '.%(ext)s';

            $tmpAac = str_replace('%(ext)s', 'm4a', $tmpFile);
            $tmpWebm = str_replace('%(ext)s', 'webm', $tmpFile);
            $tmpMp4 = str_replace('%(ext)s', 'mp4', $tmpFile);
            $tmpJpg = str_replace('%(ext)s', 'jpg', $tmpFile);
            $files = array($tmpAac, $tmpWebm, $tmpJpg, $tmpMp4);
            $ffmpeg = $this->checkDependency('ffmpeg -h');

            $cmdBase = 'youtube-dl ' . $video . ' --no-progress --print-json -q --cache-dir ' . escapeshellarg($cacheDir) . ' -o ' . escapeshellarg($tmpFile);

            if ($audioOnly) {
                $cmd = $cmdBase . ' -f 140 --audio-quality 2 --audio-format m4a -x --embed-thumbnail ';

                $this->execute($cmd);
                copy($tmpAac, $target);
            } else {

                if ($videoFormat == 'webm') {
                    $cmd = $cmdBase . ' -f 43 ';
                } elseif($videoFormat = 'mp4') {
                    $cmd = $cmdBase . ' -f 22 ';
                }

                $this->execute($cmd);

                if ($videoFormat == 'webm') {
                    copy($tmpWebm, $target);
                } elseif($videoFormat = 'mp4') {
                    copy($tmpMp4, $target);
                }
            }

            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function execute($cmd)
    {
        $cmd .= ' 2>&1';
        ob_start();
        exec($cmd, $output, $returVar);
        ob_end_clean();
        if ($returVar != 0) {
            throw new RuntimeException('Failed to execute: ' . $cmd);
        }

        return $output;
    }

    public function checkDependency($tool)
    {
        try {
            $this->execute($tool);
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }

    public function checkDependencies()
    {
        if (!$this->checkDependency('ffmpeg -h') && !$this->checkDependency('avconv -h')) {
            throw new Exception('libav or ffmpeg not found.');
        }

        if (!$this->checkDependency('youtube-dl -h')) {
            throw new Exception('youtube-dl (https://github.com/rg3/youtube-dl) not found.');
        }

        if (!$this->checkDependency('AtomicParsley -h')) {
            throw new Exception('atomicparsley not found.');
        }
    }

    public function clearExpired()
    {
        $cacheDir = realpath($this->getCacheDir());

        $iterator = new \DirectoryIterator($cacheDir);

        /* @var \SplFileInfo $fileinfo */
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() == 'ytmp') {
                if ($fileinfo->getMTime() < time() - (86400 * 7)) {
                    unlink($fileinfo->getRealPath());
                }
            }
        }

    }

}