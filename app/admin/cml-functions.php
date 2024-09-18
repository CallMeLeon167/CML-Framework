<?php

/**
 * A helper function to use traits and call their methods dynamically.
 *
 * @param string $functionName The name of the method to call from the trait.
 * @param mixed  ...$parameters The parameters to pass to the method.
 *
 * @return mixed The result of the method call, or an instance of the class with the traits if no method name is provided.
 */
function useTrait(string $functionName = "", ...$parameters): mixed
{
    $class = new class
    {
        use CML\Classes\Functions\Functions;
        use CML\Classes\Functions\Session;
    };

    return empty($functionName) ? $class : $class->$functionName(...$parameters);
}

/**
 * Adds a function to the list of AJAX functions.
 *
 * This function allows you to register one or more functions to be called via AJAX.
 * The registered functions will be stored in the global variable $cml_ajax_functions.
 */
function ajax(...$function)
{
    global $cml_ajax_functions;
    $cml_ajax_functions = array_merge($cml_ajax_functions, $function);
}


/**
 * Retrieves or sets the CML configuration values.
 *
 * @param string|array|null $config (optional) The configuration key or an array of key-value pairs to set.
 * @param string    DB_NAME The name of the MySQL database.
 * @param string    DB_USER MySQL database username for authentication.
 * @param string    DB_PASSWORD MySQL database password for authentication.
 * @param string    DB_HOST MySQL hostname or IP address.
 * @param string    DB_CHARSET Database Charset to use in creating database tables (e.g., 'utf8mb4').
 * @param bool      PRODUCTION Set the development mode. True for production, false for development.
 * @param string    NONCE_KEY A secret key used for security purposes.
 * @param bool      CML_DEBUG Enable enhanced error display for better debugging.
 * @param bool      CML_DEBUG_BAR Enable or disable the debug bar.
 * @param string    ERRORLOG_FILE Define the path to the log file.
 * @param string    APP_NAME The name of the application.
 * @param string    STYLE_PATH Path to CSS files.
 * @param string    SCRIPT_PATH Path to JavaScript files.
 * @param string    SQL_PATH Path to SQL files.
 * @param string    SITES_PATH Path to site files.
 * @param string    COMPONENTS_PATH Specify the folder path to the components.
 * @param string    CACHE_PATH Defines the path where the cache files will be stored.
 * @return mixed    The CML configuration values if no parameter is provided, or the value of the specified configuration key.
 */
function cml_config(mixed $config = null)
{
    global $cml_config;

    if (is_null($config)) {
        return $cml_config;
    }

    if (is_string($config)) {
        return isset($cml_config[$config]) ? $cml_config[$config] : null;
    }

    if (is_array($config)) {
        foreach ($config as $key => $value) {
            $cml_config[$key] = $value;
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}
