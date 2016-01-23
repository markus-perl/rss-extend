<?php
namespace RssExtend\Feed;

class Image
{

    public function hash($url)
    {
        return md5($url . gethostname() . 'RssExtend');
    }

    public function url($url)
    {
        $host = null;
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = ((!empty($_SERVER['HTTPS']) && mb_strlen($_SERVER['HTTPS']) > 0 && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $protocol . $_SERVER['HTTP_HOST'];
        } else {
            $serverUrlFile = 'data/cache/server-url';
            if (file_exists($serverUrlFile)) {
                $host = file_get_contents($serverUrlFile);
            }
        }

        if ($url && $host && substr_count($url, $host)) {
            return $url;
        }

        return $host . '/image/' . urlencode(base64_encode($url)) . '/' . $this->hash($url) . '.jpg';

    }

}