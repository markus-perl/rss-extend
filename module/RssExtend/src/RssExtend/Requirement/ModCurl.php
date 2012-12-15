<?php
namespace RssExtend\Requirement;
use RssExtend\Requirement\AbstractRequirement;

class ModCurl extends AbstractRequirement
{

    /**
     * @var string
     */
    protected $errorMessage = 'PHP curl module is not installed. Please install php-curl.';

    /**
     * @return boolean
     */
    public function checkRequirement ()
    {
        return function_exists('curl_init');
    }
}
