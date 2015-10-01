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
use RssExtend\Youtube;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use RssExtend\Feed;

class YoutubeController extends AbstractActionController
{

    /**
     * @return Youtube
     */
    public function getYoutube()
    {
        return $this->getServiceLocator()->get('RssExtend\Youtube');
    }

    /**
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    public function getCache()
    {
        return $this->getServiceLocator()->get('Zend\Cache\Storage\Adapter\Filesystem');
    }

    public function indexAction()
    {

        $audioOnly = $this->params('audioOnly') == 1;
        $extension = $audioOnly ? 'm4a' : 'webm';
        $url = base64_decode($this->params()->fromRoute('url'));
        $hash = $this->params()->fromRoute('hash');
        $expectedHash = md5($url . gethostname() . 'RssExtend');
        $youtube = $this->getYoutube();


        if ($url && $hash === $expectedHash) {
            $youtube->checkDependencies();

            $tmpFile = $youtube->getCacheFilePath($hash);

            $youtube->download($url, $tmpFile, $audioOnly);

            if (file_exists($tmpFile)) {
                set_time_limit(600);

                $fileName = preg_replace("/[^A-Za-z0-9 ]/", "", $youtube->getTitle($url));
                $fileName .= '.' . $extension;

                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . $fileName);
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($tmpFile));

                $handle = fopen($tmpFile, 'r');

                while (!feof($handle)) {
                    if ($content = fread($handle, 1024 * 1024)) {
                        echo $content;
                    }
                }

                exit;
            }
        }

    }
}
