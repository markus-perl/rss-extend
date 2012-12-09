<?php
namespace RssExtend\Requirement;
use RssExtend\Requirement\AbstractRequirement;

class CacheWriteable extends AbstractRequirement

{

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param string $cacheDir
     */
    public function __construct ($cacheDir)
    {
        $this->cacheDir = realpath($cacheDir);
    }

    /**
     * @var string
     */
    protected $errorMessage = 'cache folder %s not writable';

    /**
     * @return boolean
     */
    public function checkRequirement ()
    {
        return @touch($this->cacheDir . '/test');
    }

    /**
     * @return string
     */
    public function getErrorMessage ()
    {
        return sprintf($this->errorMessage, $this->cacheDir);
    }
}
