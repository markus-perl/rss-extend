<?php
namespace RssExtend\Feed\Parser;

use RssExtend\Exception;

class Trim extends AbstractParser
{
    const TOP_TO_BOTTOM = 'top-to-bottom';

    const BOTTOM_TO_TOP = 'bottom-to-top';

    /**
     * retruns all cursor positions of given needle
     *
     * @param string $haystack
     * @param string $needle
     * @param string $start
     * @return array[]int
     */
    private function getOffsetPositions($haystack, $needle, $start = null)
    {
        $offsetPositions = array();

        $oldOffset = -1;
        do {
            $offsetPosition = strpos($haystack, $needle, $oldOffset + 1);

            if ($offsetPosition !== false) {

                if ($start === null || $start < $offsetPosition) {
                    $offsetPositions[] = $offsetPosition;
                }

                $oldOffset = $offsetPosition;
            }

        } while ($offsetPosition !== false);

        return $offsetPositions;
    }

    /**
     * Returns the cursor offset for the given needle
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @param string $direction
     * @return int
     * @throws RssExtend_Worker_Exception
     */
    private function getOffset($haystack, $needle, $offset, $direction, $start = null)
    {
        $offsetPositions = $this->getOffsetPositions($haystack, $needle, $start);

        if ($direction == self::BOTTOM_TO_TOP) {
            $offset = max(0, count($offsetPositions) - 1) - $offset;
        }

        if (isset($offsetPositions[$offset])) {
            return $offsetPositions[$offset];
        }

        throw new Exception\RuntimeException('Offset ' . $offset . ' for needle ' . $needle . ' not found');
    }

    /**
     * @param \Zend\Feed\Reader\Entry\EntryInterface $entry
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function getContent(\Zend\Feed\Writer\Entry $entry, $index = null)
    {
        $url = $entry->getLink();

        $page = $this->getDownloader()->download($url);

        $config = $this->config;

        if ($config->from === null || $config->from->searchText === null || $config->from->offset === null || $config->from->direction === null) {
            throw new Exception\RuntimeException('Invalid trim configuration. Config from incomplete');
        }

        if ($config->to === null || $config->to->searchText === null || $config->to->offset === null || $config->to->direction === null) {
            throw new Exception\RuntimeException('Invalid trim configuration. Config from incomplete');
        }

        try {
            $start = $this->getOffset($page, $config->from->searchText, $config->from->offset, $config->from->direction) + strlen($config->from->searchText);
            $end = $this->getOffset($page, $config->to->searchText, $config->to->offset, $config->to->direction, $start);
            $content = substr($page, $start, $end - $start);

        } catch (Exception $e) {
            $content = $e->getMessage();
        }

        $content = trim($content);

        return $content;
    }

}