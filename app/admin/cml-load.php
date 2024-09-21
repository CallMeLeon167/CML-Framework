<?php

/**
 * Starts the timer to measure the execution time.
 */
$cml_script_start = microtime(true);

/**
 * Loads the Composer autoloader.
 */
if (file_exists($autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php')) {
    require_once $autoloadPath;
    unset($autoloadPath);
} else {
    die('Composer vendor is not installed.');
}

/**
 * Loads configuration variables from the cml-config file.
 * Make sure to define necessary constants in cml-config.php.
 */
if (file_exists($configPath = dirname(__DIR__) . '/config/cml-config.php')) {
    require_once $configPath;
    unset($configPath);

    define('IS_AJAX', !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', rtrim(dirname($_SERVER['SCRIPT_FILENAME'], IS_AJAX ? 3 : 1), '/\\')) . "/");
} else {
    // If the configuration file is missing, trigger a user error with a descriptive message.
    if (!defined("CLI")) {
        trigger_error('The cml-config.php file is missing. Please ensure it exists and contains the necessary configuration constants. Run <strong>php cli.php config</strong> to create the config file', E_USER_ERROR);
    }
}


/**
 * Loads framework variables from cml-vars.php
 */
if (file_exists($cml_vars = __DIR__ . '/cml-vars.php')) {
    require_once $cml_vars;
    unset($cml_vars);
}

/**
 * Loads framework functions from cml-functions.php
 */
if (file_exists($cml_functions = __DIR__ . '/cml-functions.php')) {
    require_once $cml_functions;
    unset($cml_functions);
}

/**
 * Loads helper functions from cml-helper-functions.php
 */
if (file_exists($cml_helper_functions = __DIR__ . '/cml-helper-functions.php')) {
    require_once $cml_helper_functions;
    unset($cml_helper_functions);
}

header('X-Powered-By: CML-Framework/' .  useTrait()::getFrameworkVersion() . " - PHP/" . phpversion());

/**
 * Loads error handler from cml-error.php
 */
if (file_exists($handler = __DIR__ . '/cml-error.php')) {
    require_once $handler;
    unset($handler);
}

/**
 * Loads functions from functions.php
 */
if (file_exists($functions = dirname(__DIR__, 2) . '/functions.php')) {
    require_once $functions;
    unset($functions);
}
