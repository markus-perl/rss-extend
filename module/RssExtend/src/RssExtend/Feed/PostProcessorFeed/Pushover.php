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

        if ($cacheEntry && $cacheEntry['h'] != $hash) {

            $notification = array();

            $diff = $this->computeDiff($message, $cacheEntry['m']);
            foreach ($diff['values'] as $key => $value) {

                $maskValue = $diff['mask'][$key];
                $color = $maskValue == -1 ? 'red' : $maskValue == 1 ? 'green' : '';

                $line = '<font color="' . $color . '">';
                $line .= htmlentities($value);
                $line .= '</font>';

                $notification[] = $line;
            }

            $this->send($this->feed->getName(), $notification);

        }

        $this->feed->getCache()->setItem($cacheKey, serialize(array('h' => $hash, 'm' => $message)));

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
        $data['html'] = true;

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

    /**
     * @param array $from
     * @param array $to
     * @return array
     */
    private function computeDiff(array $from, array $to)
    {
        $diffValues = array();
        $diffMask = array();

        $dm = array();
        $n1 = count($from);
        $n2 = count($to);

        for ($j = -1; $j < $n2; $j++) $dm[-1][$j] = 0;
        for ($i = -1; $i < $n1; $i++) $dm[$i][-1] = 0;
        for ($i = 0; $i < $n1; $i++) {
            for ($j = 0; $j < $n2; $j++) {
                if ($from[$i] == $to[$j]) {
                    $ad = $dm[$i - 1][$j - 1];
                    $dm[$i][$j] = $ad + 1;
                } else {
                    $a1 = $dm[$i - 1][$j];
                    $a2 = $dm[$i][$j - 1];
                    $dm[$i][$j] = max($a1, $a2);
                }
            }
        }

        $i = $n1 - 1;
        $j = $n2 - 1;
        while (($i > -1) || ($j > -1)) {
            if ($j > -1) {
                if ($dm[$i][$j - 1] == $dm[$i][$j]) {
                    $diffValues[] = $to[$j];
                    $diffMask[] = 1;
                    $j--;
                    continue;
                }
            }
            if ($i > -1) {
                if ($dm[$i - 1][$j] == $dm[$i][$j]) {
                    $diffValues[] = $from[$i];
                    $diffMask[] = -1;
                    $i--;
                    continue;
                }
            }
            {
                $diffValues[] = $from[$i];
                $diffMask[] = 0;
                $i--;
                $j--;
            }
        }

        $diffValues = array_reverse($diffValues);
        $diffMask = array_reverse($diffMask);

        return array('values' => $diffValues, 'mask' => $diffMask);
    }

}