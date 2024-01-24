<?php
namespace CML\Classes\Functions;

trait Functions{

    /**
     * The version of the CML Framework.
     *
     * @var string
     */
    private static string $cml_version = "2.6.2";

    /**
     * Retrieves the current version of the framework.
     * This function returns the current version of the framework as a string.
     *
     * @return string The current version of the framework.
     */
    public static function getFrameworkVersion():string{
        return self::$cml_version;
    } 

    /**
     * Redirect to the specified named route.
     *
     * @param string $name The name of the route to redirect to
     */
    public function toRoute(string $name) {
        global $cml_namedRoutes;
        if (isset($cml_namedRoutes[$name])) {
            $url = $this->assetUrl($cml_namedRoutes[$name]);
            header("Location: $url");
            exit;
        } else {
            trigger_error("Route with name '$name' not found.", E_USER_ERROR);
        }
    }

    /**
     * Generates a complete URL by appending the given path to the base URL.
     *
     * @param string $url The path to be appended to the base URL.
     * @return string The complete URL.
     */
    public function url(string $url = ""):string {
        return BASE_URL . $url;
    }

    /**
     * Generate an absolute URL for an asset based on the provided path.
     *
     * This function appends the provided asset path to the base URL of the script,
     * ensuring that the URL is properly formatted, including the appropriate leading slash.
     *
     * @param string $path The path to the asset, relative to the root of the application.
     * @return string The absolute URL of the asset.
     */
    public function assetUrl(string $path = ""): string {
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
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
     * Execute a method in the specified controller.
     *
     * @param string $controllerName The name of the controller in which the method should be called.
     * @param string $methodName The name of the method to be called.
     * @param array $params An optional array of parameters to be passed to the method.
     */
    public static function useController(string $controllerName, string $methodName, array $params = []) {
        $controllerClassName = 'CML\\Controllers\\' . $controllerName;
        if (class_exists($controllerClassName)) {
            $controllerInstance = new $controllerClassName();
            if (method_exists($controllerInstance, $methodName)) {
                return call_user_func([$controllerInstance, $methodName], $params);
            } else {
                trigger_error("Method $methodName not found in controller $controllerName.", E_USER_ERROR);
            }
        } else {
            trigger_error("Controller $controllerName not found. Check your controllers folder /controllers/$controllerName.php", E_USER_ERROR);
        }
    }

    /**
     * Limits the number of requests per IP address within a specified time interval.
     *
     * This method checks and restricts the number of requests that can be made from a specific IP address
     * within a given time interval. If the request count exceeds the limit, it sends an HTTP response with a
     * status code 429 (Too Many Requests) and outputs the specified message.
     *
     * @param int    $limit    The maximum number of allowed requests within the interval.
     * @param int    $interval The time interval in seconds during which the requests are counted.
     * @param string $message  The message to be output in case of exceeding the limit (default: "Too Many Requests").
     */
    public function rateLimit(int $limit, int $interval, string $message = "To many requests") {
        $this->startSession();
    
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'rate_limit:' . $ip;
    
        $data = $this->getSessionData($key);
        $count = ($data['count']++ ?? 0);

        $lastAccess = ($data['last_access'] ?? 0);
        $currentTime = time();
    
        if ($currentTime - $lastAccess >= $interval) {
            $count = 1;
            $lastAccess = $currentTime;
        } else {
            $count++;
        }
    
        $this->setSessionData($key,[
            'count' => $count,
            'last_access' => $lastAccess,
        ]);

        if ($this->getSessionData($key)['count'] > $limit) {
            http_response_code(429);
            echo json_encode(["error" => $message]);
            die;
        }
    }


    /**
     * Returns the absolute file path to the project's root directory.
     * 
     * @param string $path (optional) A path to append to the root directory.
     * @return string containing the path to the root directory.
     */
    public static function getRootPath(string $path = ''):string{
        return (dirname(__DIR__, 3) . '/' . ltrim($path, "/"));
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
     * Manages session based on the provided data.
     *
     * If $data is a string, retrieves the corresponding session value.
     * If $data is an array, sets session values based on the key-value pairs in the array.
     * If $data is neither a string nor an array, returns null.
     *
     * @param string|array $data The session data or key-value pairs.
     *
     * @return mixed|null The session data or null if not found.
     */
    public function session($data){
        $this->startSession();
        if (is_string($data)) {
            return $this->getSessionData($data);
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->setSessionData($key, $value);
            }
        } else {
            return null;
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
        return $_SESSION[$key] ?? null;
    }

    /**
     * Checks if a specific key exists in the session.
     *
     * @param string $key The key to check.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public function hasSessionData(string $key) {
        $this->startSession();
        return isset($_SESSION[$key]);
    }

    /**
     * Retrieves all data from the session.
     *
     * @return array An associative array of all session data.
     */
    public function getAllSessionData() {
        $this->startSession();
        return $_SESSION;
    }

    /**
     * Retrieves the path where session data is saved.
     *
     * @return string The session save path.
     */
    public function getSessionSavePath() {
        return session_save_path();
    }

    /**
     * Merges the given associative array with the existing session data.
     * @param array $data Associative array to merge with the session data.
     */
    public function mergeSessionData(array $data) {
        $this->startSession();
        $_SESSION = array_merge($_SESSION, $data);
    }

    /**
     * Sets the path where session data is saved.
     *
     * @param string $path The path to save session data.
     */
    public function setSessionSavePath(string $path) {
        session_save_path($path);
    }

    /**
     * Retrieves and removes a value from the session.
     *
     * @param string $key The key of the data to retrieve and remove.
     *
     * @return mixed|null The data stored under the specified key, or null if not found.
     */
    public function pullSessionData(string $key) {
        $this->startSession();
        $value = $this->getSessionData($key);
        $this->unsetSessionData($key);
        return $value;
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
     * Sets a timeout for the session to automatically expire.
     *
     * @param int $minutes The number of minutes until the session expires.
     */
    public function setSessionTimeout(int $minutes) {
        $this->startSession();
        $_SESSION['timeout'] = time() + ($minutes * 60);
    }

    /**
     * Checks if the session has timed out.
     *
     * @return bool True if the session has timed out, false otherwise.
     */
    public function isSessionTimedOut() {
        $this->startSession();
        return isset($_SESSION['timeout']) && time() > $_SESSION['timeout'];
    }

    /**
     * Regenerates the session id to prevent session fixation attacks.
     */
    public function regenerateSessionId() {
        $this->startSession();
        session_regenerate_id(true);
    }

    /**
     * Sets the session cookie parameters.
     *
     * @param int $lifetime Lifetime of the session cookie, defined in seconds.
     * @param string $path Path on the domain where the cookie will work.
     * @param string $domain Cookie domain, for example 'www.php.net'. To make cookies visible on all subdomains then the domain must be prefixed with a dot like '.php.net'.
     * @param bool $secure If true cookie will only be sent over secure connections.
     * @param bool $httponly If set to true then PHP will attempt to send the httponly flag when setting the session cookie.
     */
    public function setSessionCookieParams(int $lifetime, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = true) {
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
    }

    /**
     * Clears all values from the session.
     */
    public function clearSession() {
        $this->startSession();
        $_SESSION = array();
    }

    /**
     * Checks if the session has been started.
     *
     * @return bool True if the session has been started, false otherwise.
     */
    public function isSessionStarted() {
        return session_status() == PHP_SESSION_ACTIVE;
    }

    /**
     * Ends the session and destroys all session data.
     */
    public function endSession() {
        $this->startSession();
        session_destroy();
    }
}