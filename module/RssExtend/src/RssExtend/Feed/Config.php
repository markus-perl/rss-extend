<?php
namespace RssExtend\Feed;
ini_set('display_errors', 1);

class Config extends \Zend\Config\Config
{

    /**
     * @var string
     */
    private $directory;

    /**
     * @return string
     */
    public function getDirectory ()
    {
        return $this->directory;
    }

    public function __construct ($directory)
    {
        if (is_array($directory)) {
            parent::__construct($directory, true);
            return;
        }
        else {
            parent::__construct(array(), true);
        }

        $feedDirectory = realpath($directory);
        $this->directory = $feedDirectory;

        if ('' == $feedDirectory) {
            throw new Exception\RuntimeException('directory ' . $directory . ' not found.');
        }

        $filePaths = new \DirectoryIterator($feedDirectory);

        foreach ($filePaths as $filePath) {
            if ($filePath->isFile()) {
                $info = pathinfo($filePath);

                if ($filePath->getExtension() == 'xml') {
                    $feedConfig = \Zend\Config\Factory::fromFile($feedDirectory . '/' . $filePath, true);
                    $this->merge($feedConfig);
                }
            }
        }
    }

}
