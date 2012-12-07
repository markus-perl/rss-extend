<?php
namespace RssExtend\Feed\Parser;

use RssExtend\Feed\Feed;
use RssExtend\Downloader;
use RssExtend\Exception\DownloadException;
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
     * @var null
     */
    private $downloader = null;

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
     * @return \Zend\Feed\Reader\Feed\FeedInterface
     */
    public function fetchFeed ()
    {
        $feedContent = $this->getDownloader()->download($this->feed->getUrl(), false);
        return \Zend\Feed\Reader\Reader::importString($feedContent, null, 0);
    }

    /**
     * @return \Zend\Feed\Writer\Feed
     */
    public function getUpdatedFeed ()
    {
        $origFeed = $this->fetchFeed();

        $updatedFeed = new \Zend\Feed\Writer\Feed();

        foreach (array(
                     'title',
                     'description',
                     'link'
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
                         'description'
                     ) as $attrib) {

                $getter = 'get' . ucfirst($attrib);
                $setter = 'set' . ucfirst($attrib);
                if ($origEntry->$getter() !== null) {

                    if ($origEntry->$getter()) {
                        $entry->$setter($origEntry->$getter());
                    }
                }

            }
            try {
                $content = $this->getContent($origEntry);
            } catch (DownloadException $e) {
                $content = 'failed to fetch content: ' . $e->getMessage();
            }
            $content = strip_tags($content, '<p><br><a><img>');

            if ($content) {
                $content = str_replace(']]>', '', $content);

                $image = $this->getImage($origEntry);
                if ($image) {
                    $imageTag = '<p><img src="' . $image . '" /></p>';
                    $content = $imageTag . $content;
                }

                $entry->setContent($content);
            }

            $updatedFeed->addEntry($entry);
        }

        return $updatedFeed;
    }

    abstract protected function getContent (\Zend\Feed\Reader\Entry\EntryInterface $entry);

    /**
     * @return string
     */
    protected function getImage (\Zend\Feed\Reader\Entry\EntryInterface $entry)
    {
        return null;
    }

}