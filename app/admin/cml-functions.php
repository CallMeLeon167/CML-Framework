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
 * @param mixed|null $config (optional) The configuration key or an array of key-value pairs to set.
 * @return mixed The CML configuration values if no parameter is provided, or the value of the specified configuration key.
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
