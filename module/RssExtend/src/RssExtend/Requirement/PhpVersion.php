<?php
namespace RssExtend\Requirement;
use RssExtend\Requirement\AbstractRequirement;

class PhpVersion extends AbstractRequirement
{
    /**
     * @var string
     */
    private $version = null;

    /**
     * @param string $version
     */
    public function setVersion ($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion ()
    {
        if (null === $this->version) {
            $this->version = PHP_VERSION;
        }

        return $this->version;
    }


    /**
     * @var string
     */
    protected $errorMessage = 'PHP 5.3.15 or higher is required to run this app.';

    /**
     * @return boolean
     */
    public function checkRequirement ()
    {
        return version_compare($this->getVersion(), '5.3.15', '>=') == 1;
    }
}
