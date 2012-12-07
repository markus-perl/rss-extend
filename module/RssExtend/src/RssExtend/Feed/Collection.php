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