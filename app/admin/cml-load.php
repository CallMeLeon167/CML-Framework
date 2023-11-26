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
     * Handles error configuration based on the environment.
     *
     * This function adjusts error reporting settings based on the environment,
     * controlling the display of errors and logging them to a specified file.
     * In the production environment, errors are suppressed for security and user experience reasons.
     * In other environments, all errors are displayed, aiding in development and debugging.
     */
    $errorfile = dirname(__DIR__, 2).ERRORLOG_FILE;

    if (PRODUCTION === true) {
        mysqli_report(MYSQLI_REPORT_OFF);
        error_reporting(0);
        ini_set('display_errors', 0);
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    if (file_exists($errorfile)){
        ini_set('log_errors', 1);
        ini_set('error_log', $errorfile);
    }

?>