<?php
return array(
    'modules' => array(
        'RssExtend',
    ),
    'module_listener_options' => array(
        'config_glob_paths' => array(
            '../../../config/autoload/{,*.}{global,local}.php',
            '../config/' . APPLICATION_ENV . '.config.php'
        ),
        'module_paths' => array(
            'module',
            'vendor',
        ),
    ),
);