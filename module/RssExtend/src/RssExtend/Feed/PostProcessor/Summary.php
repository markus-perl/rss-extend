<?php
namespace RssExtend\Feed\PostProcessor;

use Zend\Feed\Writer\Entry;
use RssExtend\Feed\PostProcessor\Summary\StopWords;

class Summary extends AbstractPostProcessor
{

    /**
     * shorten to this length
     *
     * @var int
     */
    private $length = 600;

    /**
     * @param string $text
     * @return string
     */
    private function normalizeText ($text)
    {
        $text = str_replace(PHP_EOL, ' ', $text);
        $text = str_replace('"', ' ', $text);
        $text = str_replace('“', ' ', $text);
        $text = str_replace('„', ' ', $text);
        $text = str_replace(':', '.', $text);
        $text = str_replace('.', ' . ', $text);
        $text = str_replace(', ', ' ', $text);
        $text = str_replace('? ', ' ? ', $text);
        $text = str_replace('; ', ' ; ', $text);
        return $text;
    }

    /**
     * @param string $word
     * @return int
     */
    private function wordLength ($word)
    {
        return mb_strlen(trim($word));
    }

    /**
     * @param $array
     * @return int
     */
    private function arrayLength($array) {
        return mb_strlen(implode('', $array));
    }

    /**
     * @param array $sentences
     * @return string
     */
    private function createList (array $sentences)
    {
        $shorten = '<ul>';
        foreach ($sentences as $sentence) {
            if (mb_strlen($sentence)) {
                $shorten .= '<li>' . $sentence . '</li>';
            }
        }
        $shorten .= '</ul>';
        return $shorten;
    }


    public function process (Entry $entry)
    {
        $text = $entry->getContent();
        $countOrig = mb_strlen($text);

        $text = $this->normalizeText($text);

        $words = explode(' ', $text);
        $parts = $part = $sentences = array();
        $wordIgnored = false;

        $stopWords = new StopWords();

        foreach ($words as $word) {

            $addWord = true;

            if ($stopWords->isStopword($word)) {

                if (count($part)) {

                    if (count($part) > 6) {
                        $parts[] = implode(' ', $part);
                    }

                    $part = array();

                    if ($word == '.') {
                        if (count($parts)) {

                            if ($wordIgnored) {
                                $sentences[] = implode(' ... ', $parts);
                            }
                            else {
                                $sentences[] = implode(' ', $parts);
                            }

                            $wordIgnored = false;
                            $parts = array();

                            if ($this->arrayLength($sentences) > $this->length) {
                                break;
                            }
                        }
                        $addWord = false;
                        $wordIgnored = true;
                        $parts = array();
                    }
                }
            }

            if ($addWord) {
                if ($this->wordLength($word)) {
                    $part[] = trim($word);
                }
            }
        }

        $sentences[] = implode(' ... ', $parts);

        if (count($parts)) {
            $parts[] = implode(' ', $part);
            $sentences[] = implode(' ... ' . $parts);
        }

        $countShorten = $this->arrayLength($sentences);
        $sentences[] = 'Summary: ' . $countOrig . ' -> ' . $countShorten;
        $text = $this->createList($sentences);

        if ($this->config->prepend !== null) {
            $origContent = $entry->getContent();
            $text .= PHP_EOL . PHP_EOL . $origContent;
            $entry->setContent($text);
        }

        $entry->setContent($text);
        return $entry;
    }

    /**
     * @param int $length
     */
    public function setLength ($length)
    {
        $this->length = (int) $length;
    }

    /**
     * @return int
     */
    public function getLength ()
    {
        return $this->length;
    }
}