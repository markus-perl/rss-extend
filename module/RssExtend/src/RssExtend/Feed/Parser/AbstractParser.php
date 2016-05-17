<?php
namespace RssExtend\Feed\Parser;

use RssExtend\Downloader;
use RssExtend\Exception\DownloadException;
use RssExtend\Exception;
use RssExtend\Feed\Feed;
use RssExtend\ImageSize;
use Zend\Config\Config;
use RssExtend\Feed\Source;

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
     * @param \Zend\Feed\Reader\Entry\EntryInterface $entry
     * @return string
     */
    protected function getUrl(\Zend\Feed\Writer\Entry $entry)
    {
        $url = $entry->getContent();
        $validator = new \Zend\Uri\Http();
        if ($url === null || $validator->isValid($url) === false) {
            $url = $entry->getLink();
        }

        return trim($url);
    }

    /**
     * @param \RssExtend\ImageSize $imageSize
     */
    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;
    }

    /**
     * @return \RssExtend\ImageSize
     */
    public function getImageSize()
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
    public function setDownloader($downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @return Downloader
     */
    public function getDownloader()
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
    protected function getCacheKey($link, $prefix = 'feed')
    {
        return $prefix . '_' . crc32($link);
    }

    /**
     * @param \RssExtend\Feed\Feed $feed
     * @param Config $config
     */
    public function __construct(Feed $feed, Config $config = null)
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
    public function fetchFeed()
    {
        $parsers = array(
            new Source\Composer2(),
            new Source\Composer(),
            new Source\Rss()
        );

        $feedContent = null;

        /* @var Source\AbstractSource $parser */
        foreach ($parsers as $parser) {
            $parser->setDownloader($this->getDownloader());
            $parser->setCache($this->feed->getCache());
            $parser->setFeed($this->feed);

            if ($parser->isConfigAvailable()) {
                $feedContent = $parser->getRss();
                break;
            }
        }

        if (null === $feedContent) {
            throw new Exception\RuntimeException('empty feed');
        }

        $feed = \Zend\Feed\Reader\Reader::importString($feedContent);

        return $this->makeWriteable($feed);
    }

    /**
     * @param \Zend\Feed\Reader\Feed\FeedInterface $origFeed
     * @return \Zend\Feed\Writer\Feed
     */
    public function makeWriteable(\Zend\Feed\Reader\Feed\FeedInterface $origFeed)
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
    public function getUpdatedFeed(\Zend\Feed\Writer\Feed $feed)
    {

        /* @var \Zend\Feed\Writer\Entry $entry */
        foreach ($feed as $index => $entry) {

            try {
                $content = $this->getContent($entry, $index);
            } catch (DownloadException $e) {
                $content = 'failed to fetch content: ' . $e->getMessage();
            }

            $content = strip_tags($content, '<p><br><a><img>');

            if ($content) {
                $content = str_replace(']]>', '', $content);

                $plainContent = strip_tags($content);
                if (mb_strlen($plainContent)) {
                    $entry->setContentPlain($plainContent);
                }

                $imageUrl = $this->getImage($entry);
                if ($imageUrl) {
                    $imageSize = $this->getImageSize();
                    $size = $imageSize->getSizeByUrl($imageUrl);
                    $entry->setMediaThumbnail($imageUrl, $size['x'], $size['y']);

                    $imageTag = '<p><img class="rssextend" src="' . $imageUrl . '"/></p>';
                    $content = $imageTag . $content;
                }

                $entry->setContent($content);
            }
        }

        $feed->rewind();

        return $feed;
    }

    abstract protected function getContent(\Zend\Feed\Writer\Entry $entry, $index = null);

    /**
     * @return string
     */
    protected function getImage(\Zend\Feed\Writer\Entry $entry)
    {
        return null;
    }

}