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

/**
 * Sets the 'checked' attribute for an HTML element if the given value matches the check value.
 *
 * @param mixed $value The value to compare against the check value.
 * @param string $check The check value. Defaults to "true".
 * @param bool $echo Whether to echo the 'checked' attribute or return it as a string. Defaults to true.
 * @return string If $echo is true, the function echoes the 'checked' attribute. Returns the 'checked' attribute as a string.
 */
function checked($value, $check = "true", $echo = true)
{
    return _attr_helper('checked', $value, $check, $echo);
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

/**
 * Escape HTML special characters
 *
 * @param string $string The input string to escape
 * @return string The escaped string
 */
function esc_html(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Escape JavaScript string
 *
 * @param string $string The input string to escape
 * @return string The escaped string
 */
function esc_js(string $string): string
{
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Sanitize a string by removing all HTML tags
 *
 * @param string $string The input string to sanitize
 * @return string The sanitized string
 */
function sanitize_text(string $string): string
{
    return strip_tags($string);
}

/**
 * Escape PHP code and HTML for display without execution
 *
 * This function escapes both PHP code and HTML so it can be safely displayed as text,
 * preventing PHP execution and HTML rendering while maintaining its original appearance.
 *
 * @param string $php_code The PHP code to escape
 * @return string The escaped PHP code and HTML
 */
function esc_php(string $php_code): string
{
    $escaped_code = htmlspecialchars($php_code, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

    // Replace PHP opening tags
    $escaped_code = str_replace('&lt;?php', '&lt;?php', $escaped_code);
    $escaped_code = str_replace('&lt;?=', '&lt;?=', $escaped_code);
    $escaped_code = str_replace('&lt;?', '&lt;?', $escaped_code);

    // Replace PHP closing tag
    $escaped_code = str_replace('?&gt;', '?&gt;', $escaped_code);

    return $escaped_code;
}

/**
 * Escapes a string for safe use in HTML attributes.
 *
 * This function converts special characters to HTML entities to prevent
 * XSS (Cross-Site Scripting) attacks. It uses `htmlspecialchars` to convert
 * characters like `<`, `>`, `&`, and `"` to their corresponding HTML entities.
 * Additionally, it replaces single quotes with `&#039;`.
 *
 * @param string $string The string to be escaped.
 * @return string The escaped string, or an empty string if the input is empty.
 */
function esc_attr(string $string): string
{
    if (empty($string)) {
        return '';
    }

    $escaped = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    $escaped = str_replace("'", '&#039;', $escaped);

    return $escaped;
}

/**
 * Sanitize an email address
 *
 * @param string $email The email address to sanitize
 * @return string The sanitized email address
 */
function sanitize_email(string $email): string
{
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

/**
 * Validate an email address
 *
 * @param string $email The email address to validate
 * @return bool True if the email is valid, false otherwise
 */
function validate_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Sanitize a URL
 *
 * @param string $url The URL to sanitize
 * @return string The sanitized URL
 */
function sanitize_url(string $url): string
{
    return filter_var($url, FILTER_SANITIZE_URL);
}

/**
 * Validate a URL
 *
 * @param string $url The URL to validate
 * @return bool True if the URL is valid, false otherwise
 */
function validate_url(string $url): bool
{
    return (bool) filter_var($url, FILTER_VALIDATE_URL);
}

/**
 * Sanitize a filename
 *
 * @param string $filename The filename to sanitize
 * @return string The sanitized filename
 */
function sanitize_filename(string $filename): string
{
    $filename = preg_replace('/[^a-zA-Z0-9\s.-]/', '', $filename);
    $filename = preg_replace('/\.+/', '.', $filename);
    return trim($filename);
}
