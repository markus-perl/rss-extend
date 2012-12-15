<?php
namespace RssExtend\Feed\Parser;

use RssExtend\Feed\Feed;
use RssExtend\Downloader;
use RssExtend\Exception\DownloadException;
use RssExtend\ImageSize;
use \Zend\Config\Config;

abstract class AbstractParser
{

    /**
     * @var Feed
     */
    protected $feed;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var Downloader
     */
    private $downloader = null;

    /**
     * @var ImageSize
     */
    private $imageSize = null;

    /**
     * @param \RssExtend\ImageSize $imageSize
     */
    public function setImageSize ($imageSize)
    {
        $this->imageSize = $imageSize;
    }

    /**
     * @return \RssExtend\ImageSize
     */
    public function getImageSize ()
    {
        if (null === $this->imageSize) {
            $this->imageSize = new ImageSize();

            if ($this->getDownloader()) {
                $this->imageSize->setDownloader($this->getDownloader());
                $this->imageSize->setCache($this->getDownloader()->getCache());
            }
        }

        return $this->imageSize;
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
     *
     * @param string $link
     * @return string
     */
    protected function getCacheKey ($link, $prefix = 'feed')
    {
        return $prefix . '_' . crc32($link);
    }

    /**
     * @param \RssExtend\Feed\Feed $feed
     * @param Config $config
     */
    public function __construct (Feed $feed, Config $config = null)
    {
        $this->feed = $feed;

        $this->config = new Config(array());
        if ($config) {
            $this->config = $config;
        }
    }

    /**
     * @return \Zend\Feed\Writer\Feed
     */
    public function fetchFeed ()
    {
        $feedContent = $this->getDownloader()->download($this->feed->getUrl(), false);
        $feed = \Zend\Feed\Reader\Reader::importString($feedContent, null, 0);

        return $this->makeWriteable($feed);
    }

    /**
     * @param \Zend\Feed\Reader\Feed\FeedInterface $origFeed
     * @return \Zend\Feed\Writer\Feed
     */
    public function makeWriteable (\Zend\Feed\Reader\Feed\FeedInterface $origFeed)
    {
        $updatedFeed = new \Zend\Feed\Writer\Feed();

        foreach (array(
                     'title',
                     'description',
                     'link',
                     'dateCreated',
                     'dateModified',
                 ) as $attrib) {

            $getter = 'get' . ucfirst($attrib);
            $setter = 'set' . ucfirst($attrib);
            if ($origFeed->$getter()) {
                $updatedFeed->$setter($origFeed->$getter());
            }
        }

        /* @var \Zend\Feed\Reader\Entry\Atom $origEntry */
        foreach ($origFeed as $origEntry) {
            $entry = $updatedFeed->createEntry();
            foreach (array(
                         'title',
                         'link',
                         'description',
                         'dateModified',
                         'dateCreated',
                         'description',
                         'content',
                     ) as $attrib) {

                $getter = 'get' . ucfirst($attrib);
                $setter = 'set' . ucfirst($attrib);
                if ($origEntry->$getter() !== null) {
                    if ($origEntry->$getter()) {
                        $entry->$setter($origEntry->$getter());
                    }
                }
            }
            $updatedFeed->addEntry($entry);
        }

        return $updatedFeed;
    }

    /**
     * @return \Zend\Feed\Writer\Feed
     */
    public function getUpdatedFeed (\Zend\Feed\Writer\Feed $feed)
    {

        /* @var \Zend\Feed\Writer\Entry $entry */
        foreach ($feed as $entry) {

            try {
                $content = $this->getContent($entry);
            } catch (DownloadException $e) {
                $content = 'failed to fetch content: ' . $e->getMessage();
            }
            $content = strip_tags($content, '<p><br><a><img>');

            if ($content) {
                $content = str_replace(']]>', '', $content);

                $imageUrl = $this->getImage($entry);
                if ($imageUrl) {
                    $imageSize = $this->getImageSize();
                    $size = $imageSize->getSizeByUrl($imageUrl);
                    $entry->setMediaThumbnail($imageUrl, $size['x'], $size['y']);

                    $imageTag = '<p><img src="' . $imageUrl . '" /></p>';
                    $content = $imageTag . $content;
                }

                $entry->setContent($content);
            }
        }

        $feed->rewind();
        return $feed;
    }

    abstract protected function getContent (\Zend\Feed\Writer\Entry $entry);

    /**
     * @return string
     */
    protected function getImage (\Zend\Feed\Writer\Entry $entry)
    {
        return null;
    }

}