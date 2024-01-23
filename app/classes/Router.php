<?php
namespace CML\Classes;

/**
 * Class Router
 *
 * The Router class handles routing in the CML framework.
 * It matches the requested URL and HTTP method to the defined routes,
 * executes the corresponding callback functions, and handles error cases.
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
     * @var array
     */
    public array $errorPage = [];
    
    /**
     * Array of error page variables.
     *
     * @var array
     */
    public array $errorPageVariables = [];

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
     * Stores the named routes with their corresponding URLs.
     *
     * @var array
     */
    public array $namedRoutes = [];

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
    public function isApi():bool{
        header('Content-Type: application/json');
        return $this->isApi = true;
    }

    /**
     * Get all defined routes.
     *
     * @return array An array containing all defined routes.
     */
    public function getAllRoutes(): array {
        return array_map(function($method, $routes) {
            return array_map(function($url) use ($method) {
                return ['method' => $method, 'url' => $url];
            }, array_keys($routes));
        }, array_keys($this->routes), $this->routes);
    }

    /**
     * Get the value of a route parameter.
     *
     * If $paramName is empty, returns an array of all current route parameters.
     * If $paramName is specified, returns the value of the corresponding route parameter.
     *
     * @param mixed $paramName The name of the route parameter to retrieve. (optional)
     * @return mixed|array|null The value of the specified route parameter, an array of all route parameters, or null if the parameter is not found.
     */
    public function getRouteParam($paramName = null) {
        if (is_null($paramName)) {
            // Return all current route parameters
            return $this->currentRouteParams; 
        } else {
            // Return the value of the specified route parameter or null if not found
            return isset($this->currentRouteParams[$paramName]) ? $this->currentRouteParams[$paramName] : null;
        }
    }

    /**
     * Handle a route not found error.
     *
     * @param string $url The URL for the route
     * @param string $method The HTTP method (e.g., "GET" or "POST")
     */
    private function handleRouteNotFound(string $url, string $method) {
        header("HTTP/1.1 404 Not Found");
        trigger_error("Route not found for URL: '$url' (Method: $method)", E_USER_ERROR);
    }

    /**
     * Set a URL to redirect to if the route is not defined.
     *
     * @param string $url The URL for the redirect
     */
    public function setErrorRedirect(string $url){
        $this->redirectUrl = parent::assetUrl($url);
    }

    /**
     * Set a path to show an error page if the route is not defined.
     *
     * @param string $siteName The name of the desired file.
     */
    public function setErrorPage(string $siteName, array $variables = [], string $htmlTitle = "404 - Not Found"){
        if (file_exists(self::getRootPath($this->sitesPath.$siteName))) {
            $this->errorPage['page'] = $siteName;
            $this->errorPage['title'] = $htmlTitle;
            $this->errorPageVariables = $variables;
        } else {
            return trigger_error(htmlentities("Could not find the file $this->sitesPath"."$siteName", E_USER_ERROR));
        }
    }

    /**
     * Add a global middleware for routes not in the specified array of URLs.
     *
     * @param array $urls An array of URLs for the redirect
     * @param Closure $gloMiddleware
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
     * @param Closure $middleware The middleware function to be added.
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
     * @param Closure $target The callback function for the route
     * @param string $name The name for the route
     * @return object
     */
    public function addRoute($methods, string $url, \Closure $target, string $name = '') {
        $methods = (is_array($methods)) ? $methods : [$methods]; // Convert to an array if it's a single method
        $this->currentRoute = $url;

        foreach ($methods as $method) {
            $this->currentMethod = $method;
            $this->routes[$method][$url] = [
                'target' => $target,
                'name' => $name,
                'ajaxOnly' => false,
                'params' => [],
                'where' => $this->whereConditions,
            ];

            if (!empty($name)) {
                $this->namedRoutes[$name] = $url;
            }
        }

        global $cml_namedRoutes; $cml_namedRoutes = $this->namedRoutes;

        $this->whereConditions = []; // Clear where conditions

        return $this;
    }

    /**
     * Get the URL for a named route with placeholder replacement.
     *
     * @param string $name The name of the route
     * @param array $parameters Associative array of parameter values to replace placeholders
     * @return string|null The URL for the named route with replaced placeholders or null if not found
     */
    public function getNamedRouteUrl(string $name, array $parameters = []): ?string {
        if (isset($this->namedRoutes[$name])) {
            $url = $this->namedRoutes[$name];

            // Replace placeholders in the URL with actual values from the parameters array
            foreach ($parameters as $paramName => $paramValue) {
                $url = str_replace(":$paramName", $paramValue, $url);
            }

            return $url;
        }
        return null;
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
     * @param Closure $callback A function that defines the specific routes for this group and adds them to the router.
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
            } elseif (isset($this->errorPage['page'])) {
                $this->setTitle($this->errorPage['title']);
                $this->getSite($this->errorPage['page'], $this->errorPageVariables);
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
                    // Execute global middleware function
                    if (!empty($this->globalMiddleware) && !in_array($url, $this->globalMiddleware["url"])) {
                        call_user_func($this->globalMiddleware["function"][0]);
                    }
    
                    // Execute middleware functions before the target function
                    $this->executeMiddleware('before', $url);
    
                    // Call the target function with the extracted parameter values
                    $parameterValues = $this->sanitizeStringsArray(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
                    $this->currentRouteParams = $parameterValues;
                    call_user_func_array($routeData['target'], $parameterValues);
    
                    // Execute middleware functions after the target function
                    $this->executeMiddleware('after', $url);
    
                    // Close the application
                    (!$this->isApi && !$routeData['ajaxOnly']) ? parent::build_end() : exit;
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
    protected function checkWhereConditions(array $parameterValues, array $whereConditions):bool {
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
    protected function executeMiddleware(string $position, string $url) {
        if (!empty($this->middlewares)) {
            $mdPosition = array_search($url, $this->middlewares["route"]);
            if (is_int($mdPosition) && $this->middlewares['position'][$mdPosition] === $position) {
                call_user_func($this->middlewares["function"][$mdPosition]);
            }
        }
    }

    /**
     * Loads and displays a file with PHP components embedded in HTML tags.
     *
     * @param string $siteName The name of the desired file.
     * @param array $variables An associative array of variables to be made available in the loaded file.
     */
    public function getSite(string $siteName, array $variables = []) {
        $this->build();
        $sitePath = self::getRootPath($this->sitesPath . $siteName);
    
        if (!file_exists($sitePath)) {
            trigger_error(htmlentities("getSite('$siteName') | Site not found => " . $this->sitesPath . $siteName), E_USER_ERROR);
            return;
        }
    
        extract($variables);
        ob_start();
        require $sitePath;
        $content = ob_get_clean();
    
        $content = preg_replace_callback('/<(\w+)([^>]*)>([\s\S]*?)<\/\1>|<(\w+)([^>]*)>/', function($matches) {
            $tag = $matches[4] ?? $matches[1];
            $attributes = $matches[5] ?? $matches[2];
            $slot = $matches[3] ?? null;
    
            $componentName = $tag . '.cml.php';
            $componentPath = self::getRootPath(COMPONENTS_PATH . $componentName);
    
            if (file_exists($componentPath) && preg_match('~^\p{Lu}~u', $tag)) {
                $attributeValues = [];
                preg_match_all('/(\w+)="([^"]*)"/', $attributes, $attributeMatches);
                foreach ($attributeMatches[1] as $index => $attributeName) {
                    $attributeValues[$attributeName] = $attributeMatches[2][$index];
                }
    
                foreach ($attributeValues as $attributeName => $attributeValue) {
                    $$attributeName = $attributeValue;
                }
    
                if (!empty($slot)) {
                    $slot = trim($slot);
                }
    
                ob_start();
                require $componentPath;
                return ob_get_clean();
            } else {
                return $matches[0];
            }
        }, $content);
    
        echo $this->minifyHTML($content);
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
}