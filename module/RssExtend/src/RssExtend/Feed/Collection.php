<?php
namespace RssExtend\Feed;
use RssExtend\Feed\Config;
use RssExtend\Feed\Feed;

class Collection implements \Iterator, \Countable
{
    private $position = 0;

    /**
     * @var array[]Feed
     */
    private $data = array();

    /**
     * @param Feed $feed
     */
    public function addElement (Feed $feed)
    {
        $this->data[] = $feed;
    }

    public function __construct (Config $config = null)
    {
        if ($config) {
            $this->fillByConfig($config);
        }
    }

    /**
     * @param Config $config
     */
    public function fillByConfig (Config $config)
    {
        foreach ($config as $id => $entry) {
            $this->addElement(new Feed($id, $entry));
        }
    }

    public function rewind ()
    {
        $this->position = 0;
    }

    public function current ()
    {
        return $this->data[$this->position];
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
}