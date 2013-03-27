<?php
namespace RssExtend\Feed\PostProcessor\Summary;

class StopWords implements \Iterator
{

    private $position = 0;

    private $array = null;

    public function __construct ()
    {
        $this->array = file(__DIR__ . '/stopwords.txt', FILE_IGNORE_NEW_LINES);
        $this->position = 0;
    }

    public function rewind ()
    {
        $this->position = 0;
    }

    public function current ()
    {
        return $this->array[$this->position];
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
        return isset($this->array[$this->position]);
    }

    public function isStopword ($word)
    {
        reset($this->array);
        foreach ($this->array as $entry) {
            if ($entry == $word) {
                return true;
            }
        }
        return false;
    }

}