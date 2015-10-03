<?php
namespace RssExtend\Feed\Parser;

use RssExtend\Feed\Parser\Exception\RuntimeException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Youtube extends AbstractParser implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (null == $this->serviceLocator) {
            throw new RuntimeException('Service Locator not set');
        }

        return $this->serviceLocator;
    }

    /**
     * @return string
     */
    public function getVideoFormat()
    {
        $videoFormat = 'webm';
        if ($this->config->format == 'mp4') {
            $videoFormat = 'mp4';
        }
        return $videoFormat;
    }

    /**
     * @return bool
     */
    public function getAudioOnly()
    {
        return $this->config->audioOnly === 'true';
    }

    /**
     * (non-PHPdoc)
     * @see RssExtend_Worker_Abstract::_getContent()
     */
    protected function getContent(\Zend\Feed\Writer\Entry $entry, $index = null)
    {
        $url = $this->getUrl($entry);
        $html = null;
        $youtube = $this->getServiceLocator()->get('RssExtend\Youtube');

        $keepLocalEpisodes = 1;
        if ($this->config->keepLocalEpisodes !== null) {
            $keepLocalEpisodes = (int)$this->config->keepLocalEpisodes;
        }


        $hours = $minutes = $seconds = null;
        sscanf($youtube->getDuration($url), "%d:%d:%d", $hours, $minutes, $seconds);
        $durationSeconds = $seconds !== null ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

        $entry->setItunesDuration($durationSeconds);
        $entry->setEnclosure(array(
            'uri' => $this->url($entry),
            'type' => $this->getAudioOnly() ? 'audio/aac' : 'video/' . $this->getVideoFormat(),
            'length' => 1));

        if ($index < $keepLocalEpisodes) {
            $tmpFile = $youtube->getCacheFilePath($this->hash($url));
            $youtube->download($url, $tmpFile, $this->getAudioOnly(), $this->getVideoFormat());
        }

        $duration = '(' . $youtube->getDuration($url) . ') ';
        $content = '<p>' . $duration . htmlentities($youtube->getDescription($url)) . '</p>';
        $content .= '<p><a href="' . $this->url($entry) . '">Download (can take some time before the download starts)</a></p>';
        $content .= '<p><a href="' . $url . '">Video on youtube</a></p>';
        return $content;
    }

    /**
     * @return string
     */
    protected function getImage(\Zend\Feed\Writer\Entry $entry)
    {
        //Not implemented yet
        return null;
    }

    /**
     * Returns the hash for a given url
     *
     * @param string $url
     * @return string
     */
    function hash($url)
    {
        return md5($url . gethostname() . 'RssExtend');
    }

    /**
     * Download URl
     *
     * @param string $entry
     * @return string
     */
    public function url($entry)
    {
        $url = $this->getUrl($entry);
        $audioOnly = $this->getAudioOnly() ? '1' : '0';
        $format = $this->getVideoFormat();
        $host = $this->getServiceLocator()->get('RssExtend\Host')->getDomainWithProtocol();
        return $host . '/youtube/' . urlencode(base64_encode($url)) . '/' . $this->hash($url) . '/' . $audioOnly . '/' . $format;
    }
}