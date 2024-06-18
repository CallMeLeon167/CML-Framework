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
function cml_config(mixed $config = null): mixed
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

/**
 * Sets the 'selected' attribute for an HTML element if the given value matches the check value.
 *
 * @param mixed $value The value to compare against the check value.
 * @param string $check The check value. Defaults to "true".
 * @param bool $echo Whether to echo the 'selected' attribute or return it as a string. Defaults to true.
 * @return string If $echo is true, the function echoes the 'selected' attribute. Returns the 'selected' attribute as a string.
 */
function selected($value, $check = "true", $echo = true)
{
    return _attr_helper('selected', $value, $check, $echo);
}

/**
 * Sets the 'disabled' attribute for an HTML element if the given value matches the check value.
 *
 * @param mixed $value The value to compare against the check value.
 * @param string $check The check value. Defaults to "true".
 * @param bool $echo Whether to echo the 'disabled' attribute or return it as a string. Defaults to true.
 * @return string If $echo is true, the function echoes the 'disabled' attribute. Returns the 'disabled' attribute as a string.
 */
function disabled($value, $check = "true", $echo = true)
{
    return _attr_helper('disabled', $value, $check, $echo);
}

/**
 * Sets the 'readonly' attribute for an HTML element if the given value matches the check value.
 *
 * @param mixed $value The value to compare against the check value.
 * @param string $check The check value. Defaults to "true".
 * @param bool $echo Whether to echo the 'readonly' attribute or return it as a string. Defaults to true.
 * @return string If $echo is true, the function echoes the 'readonly' attribute. Returns the 'readonly' attribute as a string.
 */
function readonly($value, $check = "true", $echo = true)
{
    return _attr_helper('readonly', $value, $check, $echo);
}

/**
 * Sets the 'required' attribute for an HTML element if the given value matches the check value.
 *
 * @param mixed $value The value to compare against the check value.
 * @param string $check The check value. Defaults to "true".
 * @param bool $echo Whether to echo the 'required' attribute or return it as a string. Defaults to true.
 * @return string If $echo is true, the function echoes the 'required' attribute. Returns the 'required' attribute as a string.
 */
function required($value, $check = "true", $echo = true)
{
    return _attr_helper('required', $value, $check, $echo);
}

/**
 * Helper function to generate and optionally echo an HTML attribute if the given value matches the check value.
 *
 * @param string $attr The HTML attribute to set (e.g., 'selected', 'disabled').
 * @param mixed $value The value to compare against the check value.
 * @param string $check The check value. Defaults to "true".
 * @param bool $echo Whether to echo the attribute or return it as a string. Defaults to true.
 * @return string If $echo is true, the function echoes the attribute. Returns the attribute as a string.
 */
function _attr_helper($attr, $value, $check, $echo = true)
{
    $result = ($value == $check) ? " $attr=\"$attr\"" : '';

    if ($echo) {
        echo $result;
    }

    return $result;
}
