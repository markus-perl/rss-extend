<?php
namespace RssExtend\Feed;

use RssExtend\Feed\Exception\RuntimeException;
use RssExtend\Feed\Parser\AbstractParser;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Feed implements ServiceLocatorAwareInterface
{

    /**
     * @var \Zend\Cache\Storage\Adapter\Filesystem
     */
    private $cache = null;

    /**
     * @var \Zend\Config\Config
     */
    private $postProcess;

    /**
     * @var \Zend\Config\Config
     */
    private $postProcessFeed;

    /**
     * @var \Zend\Config\Config
     */
    private $preProcess;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $encoding = 'utf-8';

    /**
     * @var AbstractParser
     */
    private $parser;

    /**
     * @var string
     */
    private $method;

    /**
     * @var \Zend\Config\Config
     */
    private $methodConfig;

    /**
     * @var string
     */
    private $id;

    /**
     * @var \Zend\Config\Config
     */
    private $composerConfig;

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (null == $this->serviceLocator) {
            throw new RuntimeException('Service Locator not set');
        }

        return $this->serviceLocator;
    }


    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $method
     */
    public function setMethod($method, \Zend\Config\Config $methodConfig = null)
    {
        $this->method = $method;
        $this->methodConfig = $methodConfig;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return \Zend\Config\Config
     */
    public function getMethodConfig()
    {
        return $this->methodConfig;
    }

    /**
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \RssExtend\Feed\Parser\AbstractParser $parser
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return \RssExtend\Feed\Parser\AbstractParser
     */
    public function getParser()
    {
        if (null === $this->parser) {

            $method = $this->getMethod();
            $parserName = 'RssExtend\\Feed\\Parser\\' . ucfirst(mb_strtolower($method));

            if (false == class_exists($parserName)) {
                throw new Exception\RuntimeException('invalid method specified');
            }

            $parser = new $parserName($this, $this->getMethodConfig());
            $parser->getDownloader()->setCache($this->getCache());

            if ($parser instanceof ServiceLocatorAwareInterface) {
                $parser->setServiceLocator($this->getServiceLocator());
            }

            $this->setParser($parser);
        }

        return $this->parser;
    }

    /**
     * @return \Zend\Feed\Writer\Feed
     */
    public function getUpdatedFeed($ignoreCache = false)
    {
        $origFeed = $this->getParser()->fetchFeed();

        $unset = array();
        foreach ($origFeed as $key => $entry) {
            foreach ($this->getPreProcessors() as $preProcessor) {
                $newEntry = $preProcessor->process($entry);
                if ($newEntry === null) {
                    $unset[] = $key;
                }
            }
        }

        if (count($unset)) {
            rsort($unset);
            foreach ($unset as $key) {
                $origFeed->removeEntry($key);
            }
            $origFeed->orderByDate();
        }

        $feed = $this->getParser()->getUpdatedFeed($origFeed);

        $feed->setTitle($feed->getTitle() . ' - ' . \RssExtend\Version::NAME);

        $cache = $this->getCache();

        $unset = array();

        foreach ($feed as $key => $entry) {
            $id = 'entry' . crc32($entry->getLink());

            $item = null;
            if ($cache && !DEVELOPMENT && $ignoreCache !== true) {
                $item = $cache->getItem($id);
            }

            if ($item) {
                $item = unserialize($item);
                foreach (array(
                             'title',
                             'link',
                             'description',
                             'dateModified',
                             'dateCreated',
                             'description',
                             'content',
                             'mediaThumbnail',
                         ) as $attrib) {

                    $getter = 'get' . ucfirst($attrib);
                    $setter = 'set' . ucfirst($attrib);
                    if ($item->$getter() !== null) {
                        if ($item->$getter()) {
                            $entry->$setter($item->$getter());
                        }
                    }
                }
            } else {

                foreach ($this->getPostProcessors() as $postProcessor) {
                    $newEntry = $postProcessor->process($entry);

                    if ($newEntry === null) {
                        $unset[] = $key;
                    }
                }

                if ($cache) {
                    $cache->addItem($id, serialize($entry));
                }
            }

        }

        if (count($unset)) {
            rsort($unset);
            foreach ($unset as $key) {
                $feed->removeEntry($key);
            }
            $feed->orderByDate();
        }

        foreach ($this->getPostProcessorsFeed() as $postProcessor) {
            $feed = $postProcessor->process($feed);
        }

        $feed->rewind();

        return $feed;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param \Zend\Config\Config $composer
     */
    public function setComposerConfig($composer)
    {
        $this->composerConfig = $composer;
    }

    /**
     * @return \Zend\Config\Config
     */
    public function getComposerConfig()
    {
        return $this->composerConfig;
    }

    /**
     * @param \Zend\Config\Config $config
     */
    public function __construct($id = null, \Zend\Config\Config $config = null)
    {
        if ($config) {
            $this->parseConfig($config);
        }

        if ($id) {
            $this->setId($id);
        }

        if (!\Zend\Feed\Writer\Writer::isRegistered('Media')) {
            $extensions = \Zend\Feed\Writer\Writer::getExtensionManager();
            $extensions->setInvokableClass('MediaEntry', 'RssExtend\Feed\Writer\Extension\Media\Entry');
            $extensions->setInvokableClass('MediaRendererEntry', 'RssExtend\Feed\Writer\Extension\Media\Renderer\Entry');
            $extensions->setInvokableClass('MediaRendererFeed', 'RssExtend\Feed\Writer\Extension\Media\Renderer\Feed');
            $extensions->setInvokableClass('ITunesEntry', 'Zend\Feed\Writer\Extension\ITunes\Entry');
            $extensions->setInvokableClass('ITunesFeed', 'Zend\Feed\Writer\Extension\ITunes\Feed');
            $extensions->setInvokableClass('ITunesRendererEntry', 'Zend\Feed\Writer\Extension\ITunes\Renderer\Entry');
            $extensions->setInvokableClass('ITunesRendererFeed', 'Zend\Feed\Writer\Extension\ITunes\Renderer\Feed');
            \Zend\Feed\Writer\Writer::registerExtension('ITunes');
            \Zend\Feed\Writer\Writer::registerExtension('Media');

        }
    }

    /**
     * @param \Zend\Config\Config $config
     * @throws Exception\RuntimeException
     */
    public function parseConfig(\Zend\Config\Config $config)
    {
        if (null === $config->name) {
            throw new Exception\RuntimeException('name not set');
        }
        $this->setName($config->name);

        if (null === $config->url && null == $config->composer) {
            throw new Exception\RuntimeException('url not set');
        }
        $this->setUrl($config->url);

        if (null === $config->method) {
            throw new Exception\RuntimeException('method not set');
        }

        if ($config->postProcess) {
            $this->setPostProcess($config->postProcess);
        }

        if ($config->postProcessFeed) {
            $this->setPostProcessFeed($config->postProcessFeed);
        }

        if ($config->preProcess) {
            $this->setPreProcess($config->preProcess);
        }

        if ($config->composer) {
            $this->setComposerConfig($config->composer);
        }

        $method = $config->method;
        $this->setMethod($method, $config->$method);
    }

    /**
     * @param \Zend\Config\Config $postProcess
     */
    public function setPostProcess(\Zend\Config\Config $postProcess)
    {
        $this->postProcess = $postProcess;
    }

    /**
     * @return \Zend\Config\Config
     */
    public function getPostProcess()
    {
        return $this->postProcess;
    }

    /**
     * @param \Zend\Config\Config $postProcess
     */
    public function setPostProcessFeed(\Zend\Config\Config $postProcess)
    {
        $this->postProcessFeed = $postProcess;
    }

    /**
     * @return \Zend\Config\Config
     */
    public function getPostProcessFeed()
    {
        return $this->postProcessFeed;
    }

    /**
     * @param \Zend\Config\Config $postProcess
     */
    public function setPreProcess(\Zend\Config\Config $postProcess)
    {
        $this->preProcess = $postProcess;
    }

    /**
     * @return \Zend\Config\Config
     */
    public function getPreProcess()
    {
        return $this->preProcess;
    }

    /**
     * @return array[]AbstractPostProcessor
     * @throws Exception\RuntimeException
     */
    public function getPostProcessors()
    {
        if (null === $this->getPostProcess()) {
            return array();
        }

        $postProcessors = array();
        foreach ($this->getPostProcess() as $name => $config) {
            $postProcessorName = 'RssExtend\\Feed\\PostProcessor\\' . ucfirst($name);

            if (false == class_exists($postProcessorName)) {
                throw new Exception\RuntimeException('invalid post processor specified');
            }

            $postProcessors[] = new $postProcessorName($config, $this);
        }

        return $postProcessors;
    }

    /**
     * @return array[]AbstractPostProcessor
     * @throws Exception\RuntimeException
     */
    public function getPostProcessorsFeed()
    {
        if (null === $this->getPostProcessFeed()) {
            return array();
        }

        $postProcessors = array();
        foreach ($this->getPostProcessFeed() as $name => $config) {
            $postProcessorName = 'RssExtend\\Feed\\PostProcessorFeed\\' . ucfirst($name);

            if (false == class_exists($postProcessorName)) {
                throw new Exception\RuntimeException('invalid post processor specified');
            }

            $postProcessors[] = new $postProcessorName($config, $this);
        }

        return $postProcessors;
    }

    /**
     * @return array[]AbstractPostProcessor
     * @throws Exception\RuntimeException
     */
    public function getPreProcessors()
    {
        if (null === $this->getPreProcess()) {
            return array();
        }

        $preProcessors = array();
        foreach ($this->getPreProcess() as $name => $config) {
            $preProcessorName = 'RssExtend\\Feed\\PreProcessor\\' . ucfirst($name);

            if (false == class_exists($preProcessorName)) {
                throw new Exception\RuntimeException('invalid pre processor specified');
            }

            $preProcessors[] = new $preProcessorName($config, $this);
        }

        return $preProcessors;
    }

    /**
     * @param \Zend\Cache\Storage\Adapter\Filesystem $cache
     */
    public function setCache(\Zend\Cache\Storage\Adapter\Filesystem $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    public function getCache()
    {
        return $this->cache;
    }
}