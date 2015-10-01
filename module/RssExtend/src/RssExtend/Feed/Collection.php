<?php
namespace RssExtend\Feed;
use RssExtend\Exception\RuntimeException;
use RssExtend\Feed\Config;
use RssExtend\Feed\Feed;
use RssExtend\Composer\Composer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class Collection implements \Iterator, \Countable, ServiceLocatorAwareInterface
{
    private $position = 0;

    /**
     * @var \Zend\Cache\Storage\Adapter\Filesystem
     */
    private $cache = null;

    /**
     * @var array[]Feed
     */
    private $data = array();

    /**
     * @var \RssExtend\Feed\Source\Composer
     */
    private $composer = null;

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
     * @param Feed $feed
     */
    public function addElement (Feed $feed)
    {
        $this->data[] = $feed;
    }


    /**
     * @param Config $config
     */
    public function fillByConfig (Config $config)
    {
        foreach ($config as $id => $entry) {
            $feed = new Feed($id, $entry);
            $feed->setServiceLocator($this->getServiceLocator());
            $this->addElement($feed);
        }
        $this->sort();
    }

    public function sort ()
    {
        $data = $this->data;

        usort($data, function (Feed $a, Feed $b) {

            $nameA = strtolower($a->getName());
            $nameB = strtolower($b->getName());

            if ($nameA == $nameB) {
                return 0;
            }

            $tmp = array(
                $nameA,
                $nameB
            );
            sort($tmp);
            if ($tmp[0] == $nameA) {
                return -1;
            }
            return 1;
        });

        $this->data = $data;
        $this->rewind();
    }

    public function rewind ()
    {
        $this->position = 0;
    }

    public function current ()
    {
        $entry = $this->data[$this->position];
        $entry->setCache($this->getCache());
        return $entry;
    }

    public function key ()
    {
        return $this->position;
    }

    public function next ()
    {
        ++$this->position;
    }

    public function valid ()
    {
        return isset($this->data[$this->position]);
    }

    public function count ()
    {
        return count($this->data);
    }

    /**
     * @param $id
     * @return Feed
     */
    public function getById ($id)
    {
        foreach ($this as $entry) {
            if ($entry->getId() == $id) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * @param \Zend\Cache\Storage\Adapter\Filesystem $cache
     */
    public function setCache (\Zend\Cache\Storage\Adapter\Filesystem $cache)
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
     * @param \RssExtend\Feed\Source\Composer $composer
     */
    public function setComposer($composer)
    {
        $this->composer = $composer;
    }

    /**
     * @return \RssExtend\Feed\Source\Composer
     */
    public function getComposer()
    {
        return $this->composer;
    }


}