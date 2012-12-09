<?php
namespace RssExtend\Requirement;

abstract class AbstractRequirement
{

    /**
     * @var string
     */
    protected $errorMessage = null;

    /**
     * @return string
     */
    public function getErrorMessage ()
    {
        return $this->errorMessage;
    }

    /**
     * @return boolean
     */
    abstract public function checkRequirement();
}
