<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RssExtend\Controller;

use RssExtend\Downloader;
use RssExtend\Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use RssExtend\Feed;

if (!function_exists('getimagesizefromstring')) {
    function getimagesizefromstring($string_data)
    {
        $uri = 'data://application/octet-stream;base64,' . base64_encode($string_data);
        return getimagesize($uri);
    }
}

class ImageController extends AbstractActionController
{
    public function indexAction()
    {
        $width = 800;
        $delivered = false;
        $image = new Feed\Image();

        $url = base64_decode($this->params()->fromRoute('url'));
        $hash = substr($this->params()->fromRoute('hash'), 0, -4);
        $expectedHash = $image->hash($url);

        /* @var \Zend\Cache\Storage\Adapter\Filesystem $cache */
        $cache = $this->getServiceLocator()->get('Zend\Cache\Storage\Adapter\Filesystem');

        $downloader = new Downloader();
        $downloader->setCache($cache);
        $downloader->setSleep(50000, 500000);
        $downloader->setMaxDownloadSize(10);

        $url = utf8_decode($url);
        header('X-URL: ' . $url);

        try {
            $image = null;

            if (false === $downloader->isLocalFile($url)) {
                $image = $downloader->download($url);
            }

            if ($image && $hash == $expectedHash) {
                $finfo = new \finfo(FILEINFO_MIME);
                $mime = explode(';', $finfo->buffer($image));
                if ($mime && isset($mime[0]) && in_array($mime[0], array('image/jpeg', 'image/jpg', 'image/png'))) {
                    $size = getimagesizefromstring($image);
                    $origWidth = $size[0];
                    $origHeight = $size[1];
                    $width = max(min($origWidth, $width), 1);
                    $ratio = $origWidth / $origHeight;
                    $height = max(round($width / $ratio), 1);

                    $origImage = null;
                    // 2000 * 2000 px max to prevent memory leaks
                    if ($width * $height < 4000000) {
                        $origImage = @imagecreatefromstring($image);
                    }

                    if ($origImage) {
                        header('Content-Type: ' . $mime[0]);
                        header('Pragma: public');
                        header('Cache-Control: maxage=' . (86400 * 30));
                        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (86400 * 30)) . ' GMT');

                        $thumbnail = imagecreatetruecolor($width, $height);

                        if ($thumbnail) {
                            imagecopyresampled($thumbnail, $origImage, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
                            $quality = 50;
                            if ($width < 500) {
                                $quality = 70;
                            }
                            if ($width <= 300) {
                                $quality = 80;
                            }
                            ob_start();
                            imagejpeg($thumbnail, null, $quality);
                            header('Content-Type: image/jpeg');
                            header("Content-Length: " . ob_get_length());
                            ob_end_flush();
                            $delivered = true;
                        }
                    }
                }
            }
        } catch (Exception $e) {

        }

        if (!$delivered) {

            header('Content-Type: image/png');
            echo file_get_contents(getcwd() . '/public/images/notFound.png');

        }

        exit;
    }
}
