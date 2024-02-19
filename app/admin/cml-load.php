<?php 

    /**
     * Loads the Composer autoloader.
     */
    if (file_exists($autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php')) {
        require_once $autoloadPath;
    } else {
        die('Composer vendor is not installed.');
    }

    /**
     * Loads configuration variables from the cml-config file.
     * Make sure to define necessary constants in cml-config.php.
     */
    if (file_exists($configPath = dirname(__DIR__) . '/config/cml-config.php')) {
        require_once $configPath;
        define('IS_AJAX', !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', rtrim(dirname($_SERVER['SCRIPT_FILENAME'], IS_AJAX ? 3 : 1), '/\\')) . "/");
    } else {
        // If the configuration file is missing, trigger a user error with a descriptive message.
        trigger_error('The cml-config.php file is missing. Please ensure it exists and contains the necessary configuration constants.', E_USER_ERROR);
    }

    /**
     * Loads error handler from cml-error.php
     */
    if (file_exists($handler = __DIR__.'/cml-error.php')){
        require_once $handler;
    }

    if (file_exists($functions = dirname(__DIR__, 2).'/functions.php')){
        $cml_ajax_functions = array();
        /**
         * Adds a function to the list of AJAX functions.
         *
         * This function allows you to register one or more functions to be called via AJAX.
         * The registered functions will be stored in the global variable $cml_ajax_functions.
         */
        function ajax(...$function) {
            global $cml_ajax_functions;
            $cml_ajax_functions = array_merge($cml_ajax_functions, $function);
        }

        /**
         * Loads functions from functions.php
         */
        require_once $functions;
    }

?>