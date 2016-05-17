<?php
namespace RssExtend\Feed\PostProcessorFeed;

class Pushover extends AbstractPostProcessor
{

    const PRIORITY_HIGH = 1;

    const PRIORITY_LOW = -1;

    const PRIORITY_NORMAL = 0;

    public function process(\Zend\Feed\Writer\Feed $feed)
    {
        $text = '';
        $message = array();
        foreach ($feed as $entry) {
            $text .= $entry->getDescription() . $entry->getTitle();
            $message[] = $entry->getTitle() . ':' . $entry->getDescription();
        }

        $cacheKey = $this->feed->getId() . '_po4';
        $hash = crc32($text);
        $cacheEntry = unserialize($this->feed->getCache()->getItem($cacheKey));
        $updateCache = false;

        if (!$cacheEntry) {
            $updateCache = true;
        }

        if ($cacheEntry && $cacheEntry['h'] != $hash) {

            $notification = array();
            foreach ($feed as $entry) {
                $notification[] = $entry->getTitle() . ': ' . $entry->getDescription();
            }

            $notification[] = '';
            $notification[] = 'Old Data:';
            foreach ($cacheEntry['m'] as $oldMessageEntry) {
                $notification[] = $oldMessageEntry;
            }

            $this->send($this->feed->getName(), $notification);

            $updateCache = true;
        }

        if ($updateCache) {
            $this->feed->getCache()->setItem($cacheKey, serialize(array('h' => $hash, 'm' => $message)));
        }

        return $feed;
    }

    public function send($title = '', $message, $url = '', $priority = self::PRIORITY_NORMAL)
    {

        if (is_array($message)) {
            $messageString = '';
            foreach ($message as $key => $value) {
                if (is_string($key)) {
                    $messageString .= $key . ': ' . $value;
                } else {
                    $messageString .= $value;
                }

                $messageString .= PHP_EOL;
            }

            $message = $messageString;
        }

        $data = array();
        $data['token'] = $this->config->token;
        $data['title'] = mb_substr($title, 0, 100);
        $data['message'] = mb_substr($message, 0, 1000);
        $data['url'] = mb_substr($url, 0, 256);
        $data['priority'] = $priority;
        $data['user'] = $this->config->user;

        $curl = curl_init('https://api.pushover.net/1/messages.json');
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_TIMEOUT => 6,
        ));

        $result = null;
        $response = curl_exec($curl);
        if ($response) {
            $result = json_decode($response);
        }

        return $result;
    }
}