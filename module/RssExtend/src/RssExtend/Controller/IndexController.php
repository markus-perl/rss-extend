<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RssExtend\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use RssExtend\Feed;

class IndexController extends AbstractActionController
{
    public function indexAction ()
    {
        $collection = $this->getServiceLocator()->get('RssExtend\Feed\Collection');
        return new ViewModel(array(
                                  'availableFeeds' => $collection
                             ));
    }
}
