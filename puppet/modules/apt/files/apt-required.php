#!/usr/bin/php
<?php
$checkFile = '/home/vagrant/.apt-update-time';
$currTime = time();

$mtime = 0;
if (file_exists($checkFile)) {
    $mtime = file_get_contents($checkFile);
}

//3 Days
$runEvery = 86400 * 3;

if ($mtime < $currTime - $runEvery)
{
    echo 'apt-get update should run';
    file_put_contents($checkFile, $currTime);
    exit(1);
}

echo 'apt-get update is not necessary';
exit(0);