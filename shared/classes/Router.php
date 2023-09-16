<?php
namespace Classes;
use Classes\HTMLBuilder;

/**
 * @author Leon.Schmidt
 */
class Router extends HTMLBuilder{
    use \Classes\Traits\Traits;

    /**
     * Stores routes
     */
    protected array $routes = [];
    protected string $currentRoute = '';

    /**
     * Stores the current Method
     */
    protected string $currentMethod = '';

    /**
     * Stores middlewares
     */
    protected array $middlewares = [];
    protected array $globalMiddleware = [];

    /**
     * Stores aliases
     */
    protected array $aliases = [];

    /**
     * Store the url to redirect
     */
    public string $redirectUrl = "";

    /**
     * Store the bool if its an api route
     */
    public bool $isApi = false;

    /**
     * Store the params of an route
     */
    protected array $currentRouteParams = [];

    /**
     * Match the routes 
     */
    public function __destruct() {
        $this->matchRoute();
    }

    /**
     * Set a tht isApi to true
     */
    public function isApi(){
        header('Content-Type: application/json');
        return $this->isApi = true;
    }

    /**
     * This static method is called from inside of the class,
     * to close the application correctly at the end.
     */
    public static function APP_CLOSE() {
        echo PHP_EOL .'</body>';
        echo PHP_EOL .'</html>';
        exit;
    }

    public function getRouteParam($paramName) {
        return $this->currentRouteParams[$paramName] ?? null;
    }

    /**
     * This method is called from inside of matchRoute(),
     * to handle a error if a route is not available.
     *
     * @param    string     $url URL for the route
     * @param    string     $method the Method like "GET" or "POST 
     */
    private static function handleRouteNotFound(string $url, string $method) {
        header("HTTP/1.1 404 Not Found");
        echo "Site not found => Route is Wrong.<br>
        <h3>Route not found for URL: <b>$url</b> (Method: <b>$method</b>)</h3>";
        Router::APP_CLOSE();
    }

    /**
     * Set a url to redirect to a route if route is not defined
     *
     * @param    string     $url URL for the redirect
     */
    public function setErrorRedirect(string $url){
        return $this->redirectUrl = parent::assetUrl($url);
    }

    /**
     * Set a global middleware for every route that is not in the array
     *
     * @param    array      $urls URLs for the redirect
     * @param    function   $gloMiddleware
     */
    public function addGlobalMiddleware(array $urls, \Closure $gloMiddleware){
        $this->globalMiddleware['function'][] = $gloMiddleware;
        foreach ($urls as $url) {
            $this->globalMiddleware['url'][] = $url;
        }
    }

    /**
     * Add a middleware to be executed before a route callback.
     *
     * @param \Closure $middleware The middleware function to be added.
     * @return $this
     */
    public function addMiddleware(\Closure $middleware, array $params = []) {
        $this->middlewares["function"][] = $middleware;
        $this->middlewares["route"][] = $this->currentRoute;
        $this->middlewares["params"][] = $params;
        return $this;
    }

    /**
     *
     * Add a route to the application
     *
     * @param    string     $method Set the Method like "GET" or "POST. Or use "*" to use every method 
     * @param    string     $url Set URL for the route 
     * @param    function   $target Set what to do on the route
     * @param    int        $statusCode Set a HTTPS status code for the route
     * @return   object     
     *
     */
    public function addRoute(string $method, string $url, \Closure $target, int $statusCode = 0) {
        $this->currentRoute = $url; // Store the current route URL
        $this->currentMethod = $method; // Set the current method
        $this->routes[$method][$url] = [
            'target' => $target,
            'statusCode' => $statusCode,
            'ajaxOnly' => false,
            'params' => [],
        ];

        return $this;
    }

    /**
     *
     * Add a route group to the application.
     * 
     * The addGroup feature allows bundling of related routes under a common URL prefix 
     * for better structure and organization in routing.
     *
     * @param    string     $prefix Is the common initial part of the URLs for the bundled routes, for example, "/admin".
     * @param    function   $callback Is a function that defines the specific routes for this group and adds them to the router.
     * @return   object     
     *
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
     * Compares the requested method and URL to defined routes,
     * processes them, and throws an exception if no match is found.
     */
    public function matchRoute() {

        # URL Validation
        $url = $_SERVER['REQUEST_URI']; // get the URL from the curet page
        if ($url != "/") {$url = rtrim($_SERVER['REQUEST_URI'], '/');} // Remove slash from the end 
        $url = strtok($url, '?'); // Remove query parameters from the URL
        $url = str_replace(ltrim(rtrim(parent::assetUrl(""), "/"), "/"), "", $url); // add basename of the application folder
        if ($url == "/index.php") { $url = "/";} // check if url is index.php if so send to the / route
        $url = str_replace("//", "/", $url); // remove double slash from the url

        $method = $_SERVER['REQUEST_METHOD']; // indicates the client's action (GET, POST, etc.)

        $alias = $this->findOriginalUrlForAlias($url);
        if ($alias !== null) {
            $url = $alias; // Use the original URL
        }

        // Process given action on GET, POST etc.
        if (isset($this->routes[$method])) {            
            $this->processRoutes($url, $this->routes[$method]);
        }

        // Process wildcard routes for all methods
        if (isset($this->routes['*'])) {
            $this->processRoutes($url, $this->routes['*']);
        }

        // call this if route not found and redirect url is set
        if(!isset($this->routes[$method][$url]) && !empty($this->redirectUrl)){
            header("Location: ".$this->redirectUrl);
            die;
        } else if ($this->routes[$this->currentMethod][$this->currentRoute]['ajaxOnly'] == true){
            echo json_encode(["error" => "this is not an Ajax call"]);
            die;
        } else {
            $this->handleRouteNotFound($url, $method);
            die;
        }

    }

    /**
     *
     * Loop through the routes to find a match for the given URL.
     *
     * @param    string     $url route URL
     * @param    array      $routes routes with the right action
     *
     */
    protected function processRoutes(string $url, array $routes) {
        foreach ($routes as $routeUrl => $routeData) {

            // Check if the current route is restricted to AJAX requests
            if ($routeData['ajaxOnly'] && !$this->isAjaxRequest()) {
                continue; // Skip this route if not an AJAX request
            }

            // Convert route URL to a regular expression pattern for the ':' parameter
            $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $routeUrl);

            // Check if the current URL matches the pattern
            if (preg_match('#^' . $pattern . '$#', $url, $matches)) {

                // Set the HTTP status code if provided
                if ($routeData['statusCode'] > 0) {
                    http_response_code($routeData['statusCode']);
                }
                
                // Execute global middleware function
                if(!empty($this->globalMiddleware) && !in_array($url, $this->globalMiddleware["url"])) {
                    call_user_func($this->globalMiddleware["function"][0]);
                }

                // Execute middleware functions
                if(!empty($this->middlewares)){
                    $mdPosition = array_search($url, $this->middlewares["route"]);
                    if(is_int($mdPosition)) call_user_func($this->middlewares["function"][$mdPosition], $this->middlewares["params"][$mdPosition]);
                }
                
                // Call the target function with the extracted parameter values
                $this->currentRouteParams = $this->sanitizeStringsArray(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
                call_user_func_array($routeData['target'], $this->currentRouteParams);

                //Close the application
                ($this->isApi == false && $routeData['ajaxOnly'] == false) ? Router::APP_CLOSE() : exit;
            }

        }
    }

    /**
     * Executes a method in the specified controller.
     *
     * @param string $controllerName The name of the controller in which the method should be called.
     * @param string $methodName The name of the method to be called.
     * @param array $params An optional array of parameters to be passed to the method.
     */
    public function useController(string $controllerName, string $methodName, array $params = []){
        $params = empty($params) ? $this->currentRouteParams : array_merge($params, $this->currentRouteParams);
        $controllerClassName = 'Controllers\\' . $controllerName;
        if (class_exists($controllerClassName)) {
            $controllerInstance = new $controllerClassName();
            if (method_exists($controllerInstance, $methodName)) {
                return call_user_func([$controllerInstance, $methodName], $params);
            } else {
                echo "Method $methodName not found in controller $controllerName.<br>";
            }
        } else {
            echo "Controller <b>$controllerName</b> not found. Check your controllers folder <b>/controllers/$controllerName.php</b> <br>";
        }
    }

    /**
     * Loads and displays a PHP file from the current directory from the /sites subfolder.
     *
     * @param   string  $siteName The name of the desired file (without ".php" ending).
     * @param   array   $variables An associative array of variables to be made available in the loaded file.
     * 
     */
    public function getSite(string $siteName, array $variables = []) {
        $siteName = rtrim($siteName, ".php");
        $sitePath = dirname(__DIR__)."/../sites/$siteName.php";

        if (file_exists($sitePath)) {
            extract($variables); // Make the variables available
            include $sitePath;
        } else {
            header("HTTP/1.1 404 Not Found");
            echo "getSite('$siteName') | Site not found => /sites/$siteName.php";
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
     * Retrieves and filters query parameters from the current request URI.
     *
     * @param string ...$desiredParams A variable number of parameter names to filter the query parameters.
     * @return array An associative array containing the filtered query parameters.
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

            // Return the filtered parameters.
            return $filteredParams;
        }

        // Return all query parameters if no desired parameters were provided.
        return $queryParams;
    }

    /**
     * Check if the current request is an AJAX request.
     *
     * @return bool
     */
    protected function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
?>