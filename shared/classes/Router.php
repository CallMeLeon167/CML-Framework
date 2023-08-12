<?php
namespace Classes;
use Classes\HTMLBuilder;

/**
 * @author Leon.Schmidt
 */
class Router {

    /**
     * Stores routes
     */
    protected array $routes = [];

    /**
     * Store the name of the project
     */
    public string $projectName = "";

    /**
     * Store the url to redirect
     */
    public string $redirectUrl = "";

    /**
     * Set the name of the tool
     */
    public function __construct($projectName) {
        $this->projectName = $projectName;
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

    /**
     * This method is called from inside of matchRoute(),
     * to handle a error if a route is not available.
     *
     * @param    string     $url URL for the route
     * @param    string     $method the Method like "GET" or "POST 
     */
    private static function handleRouteNotFound(string $url, string $method) {
        header("HTTP/1.1 404 Not Found");
        echo "Site not found.<br>
        <h3>Route not found for URL: <b>$url</b> (Method: <b>$method</b>)</h3>";
        Router::APP_CLOSE();
    }

    /**
     * Set a url to redirect to a route if route is not defined
     *
     * @param    string     $url URL for the redirect
     */
    public function setErrorRedirect(string $url){
        return $this->redirectUrl = $url;
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
        return $this->routes[$method][$url] = [
            'target' => $target,
            'statusCode' => $statusCode
        ];
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
        return $callback($this, $prefix);
    }

    /**
     * Compares the requested method and URL to defined routes,
     * processes them, and throws an exception if no match is found.
     */
    public function matchRoute() {

        # URL Validation
        $url = $_SERVER['REQUEST_URI']; // get the URL from the curet page
        if ($url != "/") {rtrim($_SERVER['REQUEST_URI'], '/');} // Remove slash from the end 
        $url = strtok($url, '?'); // Remove query parameters from the URL
        $url = str_replace("/$this->projectName", "", $url); // add basename of the application folder
        if ($url == "/index.php") { $url = "/";} // check if url is index.php if so send to the / route

        $method = $_SERVER['REQUEST_METHOD']; // indicates the client's action (GET, POST, etc.)

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
            $redirect = "/".$this->projectName.$this->redirectUrl;
            header("Location: ".$redirect);
            die;
        } else {
            // call this method if route not found
            $this->handleRouteNotFound($url, $method);
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

            // Convert route URL to a regular expression pattern for the ':' parameter
            $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $routeUrl);

            // Check if the current URL matches the pattern
            if (preg_match('#^' . $pattern . '$#', $url, $matches)) {

                // Filter and extract named parameter values from the matches
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); 

                // Set the HTTP status code if provided
                if ($routeData['statusCode'] > 0) {
                    http_response_code($routeData['statusCode']);
                }

                // Call the target function with the extracted parameter values
                call_user_func_array($routeData['target'], $this->sanitizeStringsArray($params));
                
                //Close the application
                Router::APP_CLOSE();
            }

        }
    }

    /**
     * Loads and displays a PHP file from the current directory from the /sites subfolder.
     *
     * @param   string  $siteName The name of the desired file (without ".php" ending).
     * @param   array   $variables An associative array of variables to be made available in the loaded file.
     * 
     */
    public function getSite($siteName, $variables = []) {
        $sitePath = "../".$this->projectName."/sites/$siteName.php";

        if (file_exists($sitePath)) {
            extract($variables); // Make the variables available
            include $sitePath;
        } else {
            header("HTTP/1.1 404 Not Found");
            echo "Site not found.";
        }
    }

    /**
     * Sanitize an array of strings using various filters.
     *
     * @param array $inputArray The array of strings to sanitize.
     * @return array The sanitized array.
     */
    protected function sanitizeStringsArray(array $inputArray): array {
        $sanitizedArray = [];

        foreach ($inputArray as $input) {
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

            $sanitizedArray[] = $sanitized;
        }

        return $sanitizedArray;
    }
}
?>