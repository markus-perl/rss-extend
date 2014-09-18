<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
ini_set('display_errors', 1);

define('DEVELOPMENT', is_dir('/vagrant'));

// Setup autoloading
require 'init_autoloader.php';

if (file_exists(__DIR__ . '/../library/xhprof_lib/utils/xhprof_lib.php')) {
    require_once __DIR__ . '/../library/xhprof_lib/utils/xhprof_lib.php';
    require_once __DIR__ . '/../library/xhprof_lib/utils/xhprof_runs.php';

    if (function_exists('xhprof_enable')) {
        $config = parse_ini_file('/etc/php5/conf.d/xhprof.ini');

        if ($config['xhprof.enabled']) {

            $id = uniqid();
            header('XHProf: ' . $config['xhprof.url'] . $id);
            xhprof_enable(XHPROF_FLAGS_MEMORY + XHPROF_FLAGS_CPU);

            register_shutdown_function(
                function () use ($id) {
                    $run = new XHProfRuns_Default();
                    $data = xhprof_disable();
                    $run->save_run($data, '', $id);
                });
        }
    }
}

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
