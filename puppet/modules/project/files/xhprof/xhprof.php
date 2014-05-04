#!/usr/bin/php
<?php

$target = '/etc/php5/conf.d/xhprof.ini';

if (! isset($argv[1])) {
    echo $argv[0] . ' <on|off>';
} else {
    if ($argv[1] == 'on') {
        echo 'Caution. XHProf will slow down your unit tests!' . PHP_EOL;
        system('sudo cp /vagrant/puppet/modules/project/files/xhprof/xhprof.ini ' . $target);
        system('sudo /etc/init.d/php5-fpm restart    >/dev/null');
        echo 'xhprof is now enabled' . PHP_EOL;
    } else {

        if (file_exists($target)) {
            system('sudo rm ' . $target);
        }

        system('sudo /etc/init.d/php5-fpm restart    >/dev/null');
        echo 'xhprof is now disabled' . PHP_EOL;
    }

}


echo PHP_EOL;