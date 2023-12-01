<?php 

require_once dirname(__DIR__, 2).'/vendor/autoload.php'; 

    /**
     * Loads configuration variables from the cml-config file.
     * Make sure to define necessary constants in cml-config.php.
     */
    if (file_exists($configPath = dirname(__DIR__) . '/config/cml-config.php')) {
        require_once $configPath;
    } else {
        // If the configuration file is missing, trigger a user error with a descriptive message.
        trigger_error('The cml-config.php file is missing. Please ensure it exists and contains the necessary configuration constants.', E_USER_ERROR);
    }

    /**
     * Loads functions from functions.php
     */
    if (file_exists($functions = dirname(__DIR__, 2).'/functions.php')){
        require_once $functions;
    }

    /**
     * Loads error handler from cml-error.php
     */
    if (file_exists($handler = __DIR__.'/cml-error.php')){
        require_once $handler;
    }

?>