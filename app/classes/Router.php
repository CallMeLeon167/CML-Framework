<?php
namespace CML\Classes;

/**
 * Class Router
 *
 * @author CallMeLeon <kontakt@callmeleon.de>
 * @see https://docs.callmeleon.de/cml#router
 */
class Router extends \CML\Classes\HTMLBuilder{
    use Functions\Functions;
    use Functions\Session;

    /**
     * Stores the defined routes.
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * Stores the currently requested route.
     *
     * @var string
     */
    protected string $currentRoute = '';

    /**
     * Stores the current HTTP request method.
     *
     * @var string
     */
    protected string $currentMethod = '';

    /**
     * Stores route-specific middleware functions.
     *
     * @var array
     */
    protected array $middlewares = [];

    /**
     * Stores global middleware functions.
     *
     * @var array
     */
    protected array $globalMiddleware = [];

    /**
     * Stores route aliases.
     *
     * @var array
     */
    protected array $aliases = [];

    /**
     * Stores the URL to redirect to if a route is not defined.
     *
     * @var string
     */
    public string $redirectUrl = "";

    /**
     * Stores the Page to show if a route is not defined.
     *
     * @var string
     */
    public string $errorPage = "";

    /**
     * Indicates whether the route is an API route.
     *
     * @var bool
     */
    public bool $isApi = false;

    /**
     * Stores the parameters of the current route.
     *
     * @var array
     */
    protected array $currentRouteParams = [];

    /**
     * Stores sites path.
     *
     * @var string
     */
    public string $sitesPath;
    
    /**
     * An array to store "where" conditions for route parameters.
     *
     * @var array
     */
    protected $whereConditions = [];

    /**
     * Initializes the error reporting configuration based on the PRODUCTION environment variable.
     */
    public function __construct(){
        $this->sitesPath = SITES_PATH ?? '';
    }

    /**
     * Match the defined routes.
     */
    public function __destruct() {
        $this->matchRoute();
    }

    /**
     * Set the route as an API route.
     *
     * @return bool
     */
    public function isApi(){
        header('Content-Type: application/json');
        return $this->isApi = true;
    }

    /**
     * Get a route parameter by name.
     *
     * @param string $paramName
     * @return mixed|null
     */
    public function getRouteParam($paramName) {
        return $this->currentRouteParams[$paramName] ?? null;
    }

    /**
     * Handle a route not found error.
     *
     * @param string $url The URL for the route
     * @param string $method The HTTP method (e.g., "GET" or "POST")
     */
    private function handleRouteNotFound(string $url, string $method) {
        header("HTTP/1.1 404 Not Found");
        trigger_error("Site not found => Route is Wrong.<br>
        <h3>Route not found for URL: <b>$url</b> (Method: <b>$method</b>)</h3>", E_USER_ERROR);
    }

    /**
     * Set a URL to redirect to if the route is not defined.
     *
     * @param string $url The URL for the redirect
     */
    public function setErrorRedirect(string $url){
        return $this->redirectUrl = parent::assetUrl($url);
    }

    /**
     * Set a path to show an error page if the route is not defined.
     *
     * @param string $siteName The name of the desired file.
     */
    public function setErrorPage(string $siteName){
        $sitePath = self::getRootPath($this->sitesPath.$siteName);

        if (file_exists($sitePath)) {
            return $this->errorPage = $sitePath;
        } else {
            trigger_error(htmlentities("Could not find the file $this->sitesPath.$siteName", E_USER_ERROR));
        }
    }

    /**
     * Add a global middleware for routes not in the specified array of URLs.
     *
     * @param array $urls An array of URLs for the redirect
     * @param \Closure $gloMiddleware
     */
    public function addGlobalMiddleware(array $urls, \Closure $gloMiddleware){
        $this->globalMiddleware['function'][] = $gloMiddleware;
        foreach ($urls as $url) {
            $this->globalMiddleware['url'][] = $url;
        }
    }

    /**
     * Add a middleware to be executed before or after a route callback.
     *
     * @param \Closure $middleware The middleware function to be added.
     * @param string $position The position (before or after)
     * @return $this
     */
    public function addMiddleware(\Closure $middleware, string $position = "before") {
        $this->middlewares["function"][] = $middleware;
        $this->middlewares["route"][] = $this->currentRoute;
        $this->middlewares["position"][] = $position;
        return $this;
    }

    /**
     * Add a route to the application with multiple HTTP methods.
     *
     * @param array|string $methods The HTTP methods (e.g., ['GET', 'POST']) or a single method as a string.
     * @param string $url The URL for the route
     * @param \Closure $target The callback function for the route
     * @param int $statusCode The HTTP status code for the route
     * @return object
     */
    public function addRoute($methods, string $url, \Closure $target, int $statusCode = 0){
        $methods = (is_array($methods)) ? $methods : [$methods]; // Convert to an array if it's a single method
        $this->currentRoute = $url;

        foreach ($methods as $method) {
            $this->currentMethod = $method;
            $this->routes[$method][$url] = [
                'target' => $target,
                'statusCode' => $statusCode,
                'ajaxOnly' => false,
                'params' => [],
                'where' => $this->whereConditions, // Use where conditions from the router instance
            ];
        }

        $this->whereConditions = []; // Clear where conditions

        return $this;
    }
    
    /**
     * Add a "where" condition for a route parameter.
     *
     * @param string $param The name of the parameter to which the condition applies.
     * @param string $condition The regular expression condition for the parameter.
     *
     * @return $this The router instance for method chaining.
     */
    public function where(string $param, string $condition) {
        $this->whereConditions[$param] = $condition;
        return $this;
    }


    /**
     * Add a route group to the application.
     *
     * The addGroup feature allows bundling of related routes under a common URL prefix for better structure and organization in routing.
     *
     * @param string $prefix The common initial part of the URLs for the bundled routes (e.g., "/admin").
     * @param \Closure $callback A function that defines the specific routes for this group and adds them to the router.
     * @return object
     */
    public function addGroup(string $prefix, \Closure $callback) {
        // Backup current middlewares
        $originalMiddlewares = $this->middlewares;

        // Add the group prefix to the current route
        $this->currentRoute = $prefix . $this->currentRoute;

        // Execute the callback to define routes within the group
        $callback($this, $prefix);

        // Restore the original middlewares and apply them to all routes within the group
        $this->middlewares = $originalMiddlewares;

        return $this;
    }

    /**
     * Compares the requested method and URL to defined routes, processes them, and throws an exception if no match is found.
     */
    protected function matchRoute() {

        # URL Validation
        $url = $_SERVER['REQUEST_URI']; // Get the URL from the current page
        if ($url != "/") {$url = rtrim($_SERVER['REQUEST_URI'], '/');} // Remove slash from the end
        $url = strtok($url, '?'); // Remove query parameters from the URL
        $url = str_replace(ltrim(rtrim(parent::assetUrl(""), "/"), "/"), "", $url); // Add the basename of the application folder
        if ($url == "/index.php") { $url = "/";} // Check if the URL is "index.php" and redirect to the root route
        $url = str_replace("//", "/", $url); // Remove double slashes from the URL

        $method = $_SERVER['REQUEST_METHOD']; // Indicates the client's HTTP request method (e.g., GET, POST, etc.)

        $alias = $this->findOriginalUrlForAlias($url);
        if ($alias !== null) {
            $url = $alias; // Use the original URL
        }

        // Process the given action based on the HTTP request method (e.g., GET, POST, etc.)
        if (isset($this->routes[$method])) {
            $this->processRoutes($url, $this->routes[$method]);
        }

        // Process wildcard routes for all methods
        if (isset($this->routes['*'])) {
            $this->processRoutes($url, $this->routes['*']);
        }

        // Redirect to the specified URL if the route is not found and a redirect URL is set
        if (!isset($this->routes[$method][$url])) {
            if (!empty($this->redirectUrl)) {
                header("Location: " . $this->redirectUrl);
                die;
            } elseif (!empty($this->errorPage)) {
                parent::build();
                include($this->errorPage);
                parent::build_end();
            } else {
                $this->handleRouteNotFound($url, $method);
            }
        }
    }

    /**
     * Loop through the routes to find a match for the given URL.
     *
     * @param string $url The route URL
     * @param array $routes Routes with the appropriate action
     */
    protected function processRoutes(string $url, array $routes) {
        foreach ($routes as $routeUrl => $routeData) {
            // Check if the current route is restricted to AJAX requests
            if ($routeData['ajaxOnly'] && !$this->isAjaxRequest()) {
                http_response_code(403);
                continue; // Skip this route if it's not an AJAX request
            }
    
            // Convert route URL to a regular expression pattern for ':' parameters
            $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $routeUrl);
    
            // Check if the current URL matches the pattern
            if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
                // Merge "where" conditions from the route with global "where" conditions
                $whereConditions = array_merge($routeData['where'], $this->whereConditions);
    
                // Check "where" conditions
                if ($this->checkWhereConditions($matches, $whereConditions)) {
                    // Set the HTTP status code if provided
                    if ($routeData['statusCode'] > 0) {
                        http_response_code($routeData['statusCode']);
                    }
    
                    // Execute global middleware function
                    if (!empty($this->globalMiddleware) && !in_array($url, $this->globalMiddleware["url"])) {
                        call_user_func($this->globalMiddleware["function"][0]);
                    }
    
                    // Execute middleware functions before the target function
                    $this->executeMiddleware('before', $url);
    
                    // Call the target function with the extracted parameter values
                    $parameterValues = $this->sanitizeStringsArray(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
                    call_user_func_array($routeData['target'], $parameterValues);
    
                    // Execute middleware functions after the target function
                    $this->executeMiddleware('after', $url);
    
                    // Close the application
                    ($this->isApi == false && $routeData['ajaxOnly'] == false) ? parent::build_end() : exit;
                }
            }
        }
    }
    
    /**
     * Check if parameter values meet the specified "where" conditions.
     *
     * @param array $parameterValues An array of parameter values to be checked.
     * @param array $whereConditions An array of "where" conditions for parameter validation.
     *
     * @return bool True if all conditions are met, false otherwise.
     */
    protected function checkWhereConditions(array $parameterValues, array $whereConditions) {
        foreach ($whereConditions as $paramName => $condition) {
            if (isset($parameterValues[$paramName]) && !preg_match($condition, $parameterValues[$paramName])) {
                return false;
            }
        }
        return true;
    }


    /**
     * Execute middleware functions based on the specified position (before or after).
     *
     * @param string $position The position of the middleware (before or after)
     * @param string $url The URL for which the middleware should be executed
     */
    protected function executeMiddleware($position, $url) {
        if (!empty($this->middlewares)) {
            $mdPosition = array_search($url, $this->middlewares["route"]);
            if (is_int($mdPosition) && $this->middlewares['position'][$mdPosition] === $position) {
                call_user_func($this->middlewares["function"][$mdPosition]);
            }
        }
    }

    /**
     * Execute a method in the specified controller.
     *
     * @param string $controllerName The name of the controller in which the method should be called.
     * @param string $methodName The name of the method to be called.
     * @param array $params An optional array of parameters to be passed to the method.
     */
    public function useController(string $controllerName, string $methodName, array $params = []) {
        $params = empty($params) ? $this->currentRouteParams : array_merge($params, $this->currentRouteParams);
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
     * Loads and displays a file.
     *
     * @param string $siteName The name of the desired file.
     * @param array $variables An associative array of variables to be made available in the loaded file.
     */
    public function getSite(string $siteName, array $variables = []) {
        $sitePath = self::getRootPath($this->sitesPath.$siteName);
        if (file_exists($sitePath)) {
            extract($variables); // Make the variables available
            ob_start();
            include $sitePath;
            echo $this->minifyHTML(ob_get_clean());
        } else {
            trigger_error(htmlentities("getSite('$siteName') | Site not found => ".$this->sitesPath.$siteName), E_USER_ERROR);
        }
    }

    /**
     * Set the route to be accessible only via AJAX requests.
     *
     * @return $this
     */
    public function onlyAjax() {
        if (!empty($this->currentRoute)) {
            $this->routes[$this->currentMethod][$this->currentRoute]['ajaxOnly'] = true;
        }

        return $this;
    }

    /**
     * Check if the current request is an AJAX request.
     *
     * @return bool
     */
    public function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Add an alias for the current route.
     *
     * @param string $alias The alias URL
     * @return Router This router instance
     */
    public function setAlias(string $alias) {
        if (!empty($this->currentRoute)) {
            $this->aliases[$alias] = $this->currentRoute;
        }

        return $this;
    }

    /**
     * Find the original URL for a given alias.
     *
     * @param string $alias The alias URL
     * @return string|null The original URL if found, null otherwise
     */
    protected function findOriginalUrlForAlias(string $alias) {
        return $this->aliases[$alias] ?? null;
    }

    /**
     * Sanitize an array of strings using various filters.
     *
     * @param array $inputArray The array of strings to sanitize.
     * @return array The sanitized array.
     */
    protected function sanitizeStringsArray(array $inputArray): array {
        $sanitizedArray = [];

        foreach ($inputArray as $k => $input) {
            // Remove HTML tags
            $sanitized = strip_tags($input);

            // Convert illegal characters
            $sanitized = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

            // Allow only certain characters (example: letters, numbers, spaces)
            $sanitized = preg_replace('/[^a-zA-Z0-9\s]/', '', $sanitized);

            // Replace unauthorized SQL keywords
            $sql_keywords = ["SELECT", "INSERT", "UPDATE", "DELETE", "DROP", "TABLE", "UNION"];
            $sanitized = str_ireplace($sql_keywords, "", $sanitized);

            // Add more customizations or filters as needed.

            $sanitizedArray[$k] = $sanitized;
        }

        return $sanitizedArray;
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
}