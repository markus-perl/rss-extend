<?php
namespace RssExtend;

class Vagrant
{

    /**
     * File Cache leeren
     */
    public static function clearCache ()
    {
        if (self::isVagrant()) {
            system('echo 2 | sudo tee /proc/sys/vm/drop_caches >/dev/null');
        }
    }

    /**
     * @return bool Befinden wir uns in einem Vagrant Enironment?
     */
    public static function isVagrant ()
    {
        return is_dir('/vagrant');
    }

}