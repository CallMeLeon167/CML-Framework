<?php
namespace CML\Classes\Functions;

trait Functions{
    /**
     * Generate an absolute URL for an asset based on the provided path.
     *
     * This function appends the provided asset path to the base URL of the script,
     * ensuring that the URL is properly formatted, including the appropriate leading slash.
     *
     * @param string $path The path to the asset, relative to the root of the application.
     * @return string The absolute URL of the asset.
     */
    public function assetUrl(string $path): string {
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        return ($baseUrl == "/") ? "/" . ltrim($path, '/') : $baseUrl . "/" . ltrim($path, '/');
    }

    /**
     * Retrieves and filters query parameters from the current request URI.
     *
     * @param string ...$desiredParams A variable number of parameter names to filter the query parameters.
     * @return mixed An array containing the filtered query parameters, or a single parameter value if only one is requested.
     */
    public function getQueryParams(string ...$desiredParams) {
        // Initialize an array to store query parameters.
        $queryParams = array();

        // Extract the query string from the request URI.
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

        // Parse the query string into the $queryParams array.
        if ($queryString) {
            parse_str($queryString, $queryParams);
        }

        // Check if desired parameters were provided.
        if (!empty($desiredParams)) {
            // If only one parameter is requested, return its value directly.
            if (count($desiredParams) === 1 && isset($queryParams[$desiredParams[0]])) {
                return $queryParams[$desiredParams[0]];
            }

            // Initialize an array to store filtered parameters.
            $filteredParams = array();

            // Loop through each desired parameter.
            foreach ($desiredParams as $param) {
                // Check if the parameter exists in the query parameters.
                if (isset($queryParams[$param])) {
                    // Add the parameter and its value to the filteredParams array.
                    $filteredParams[$param] = $queryParams[$param];
                }
            }

            // Return the filtered parameters as an array.
            return $filteredParams;
        }

        // Return all query parameters if no desired parameters were provided.
        return $queryParams;
    }


    /**
     * Returns the absolute file path to the project's root directory.
     * 
     * @param string $path (optional) A path to append to the root directory.
     * @return string containing the path to the root directory.
     */
    public function getRootPath(string $path = ''){
        return (dirname(__DIR__, 3) . '/' . $path);
    }

    /**
     * Check if the current request is an AJAX request.
     *
     * @return bool
     */
    public function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

/**
 * A trait for managing sessions in PHP.
 */
trait Session {
    /**
     * Starts or resumes a session if not already started.
     */
    public function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Sets a value in the session.
     *
     * @param string $key The key under which the data will be stored.
     * @param mixed $value The data to be stored in the session.
     */
    public function setSessionData(string $key, $value) {
        $this->startSession();
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieves a value from the session.
     *
     * @param string $key The key to retrieve data from.
     *
     * @return mixed|null The data stored under the specified key, or null if not found.
     */
    public function getSessionData(string $key) {
        $this->startSession();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    }

    /**
     * Unsets (removes) a value from the session.
     *
     * @param string $key The key of the data to remove.
     */
    public function unsetSessionData(string $key) {
        $this->startSession();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Ends the session and destroys all session data.
     */
    public function endSession() {
        $this->startSession();
        session_destroy();
    }
}