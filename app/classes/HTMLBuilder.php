<?php 
namespace CML\Classes;

/**
 * Class HTMLBuilder
 *
 * HTMLBuilder provides methods for building and manipulating HTML documents.
 * It includes functionality for adding headers, footers, styles, scripts, metas, CDNs, and hooks to the HTML document.
 * The class also supports HTML minification and provides methods for setting the project name, title, favicon, and tag attributes.
 * Additionally, it includes methods for rendering components and adding content to the HTML document.
 *
 * @author CallMeLeon <kontakt@callmeleon.de>
 * @see https://docs.callmeleon.de/cml#html-builder
 */
abstract class HTMLBuilder {
    use Functions\Functions;

    const BEFORE_HEAD = 'before_head';
    const TOP_HEAD = 'top_head';
    const BOTTOM_HEAD = 'bottom_head';
    const AFTER_HEAD = 'after_head';
    const BEFORE_BODY = 'before_body';
    const TOP_BODY = 'top_body';
    const BOTTOM_BODY = 'bottom_body';
    const AFTER_BODY = 'after_body';

    /**
     * @var bool Indicates whether the HTML has been built or not.
     */
    private bool $builded = false;

    /**
     * @var bool Indicates whether the HTML should be minified or not.
     */
    private bool $minifyHTML = false;

    /**
     * @var string The URL for AJAX requests.
     */
    private string $ajaxUrl = "";

    /**
     * @var string The name of the JavaScript variable to store the Ajax URL
     */
    private string $ajaxVar = "";

    /**
     * @var string The name of the project.
     */
    private string $projectName = "";

    /**
     * @var string The title of the web page.
     */
    private string $title = "";

    /**
     * @var string The path to the favicon image.
     */
    private string $favicon = "";

    /**
     * @var string The HTML code for the header section.
     */
    private string $header = "";

    /**
     * @var string The HTML code for the footer section.
     */
    private string $footer = "";
    
    /**
     * @var string The language of the web page.
     */
    private string $langAttr = "en";
    
    /**
     * @var string The character encoding for the web page.
     */
    private string $charsetAttr = "UTF-8";

    /**
     * @var array The attributes for the body tag.
     */
    private array $bodyAttr = [];

    /**
     * @var array The attributes for the html tag.
     */
    private array $htmlAttr = [];

    /**
     * @var array An array of stylesheets to be included in the web page.
     */
    private array $styles = [];

    /**
     * @var array An array of JavaScript files to be included in the web page.
     */
    private array $scripts = [];

    /**
     * @var array An array of meta tags to be included in the head section of the web page.
     */
    private array $metas = [];

    /**
     * @var array An array of CDNs (Content Delivery Networks) to be included in the web page.
     */
    private array $cdns = [];

    /**
     * @var array An array of hooks for customizing the HTML output.
     */
    private array $hooks = [];

    /**
     * @var array An array of predefined hooks for customizing the HTML output.
     */
    private array $regHooks = [
        self::BEFORE_HEAD,
        self::TOP_HEAD,
        self::BOTTOM_HEAD,
        self::AFTER_HEAD,
        self::BEFORE_BODY,
        self::TOP_BODY,
        self::BOTTOM_BODY,
        self::AFTER_BODY,
    ];

    /**
     * Activates HTML minification.
     */
    public function activateMinifyHTML() {
        $this->minifyHTML = true;
    }

    /**
     * Sets the project name and updates the title accordingly.
     *
     * @param string $projectName The project name.
     */
    public function setProjectName(string $projectName) {
        $this->projectName = $projectName;
        $this->setTitle($this->projectName);
    }

    /**
     * Sets the title of the HTML document.
     *
     * @param string $title The title of the HTML document.
     */
    public function setTitle(string $title) {
        $this->title = $title;
    }

    /**
     * Sets the path to the favicon.
     *
     * @param string $favicon The path to the favicon.
     */
    public function setFavicon(string $favicon) {
        $this->favicon = $favicon;
    }

    /**
     * Adds a header element to the HTML document.
     *
     * @param string|array $header The header element to add  or variables if array.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    public function addHeader($header = '', array $variables = []) {
        $this->_addContent(COMPONENTS_PATH.'header.php', $header, $this->header, $variables);
    }

    /**
     * Removes the header from the HTML document.
     */
    public function removeHeader(){
        $this->header = "";
    }

    /**
     * Adds a footer element to the HTML document.
     *
     * @param string|array $footer The footer element to add  or variables if array.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    public function addFooter($footer = '', array $variables = []) {
        $this->_addContent(COMPONENTS_PATH.'footer.php', $footer, $this->footer, $variables);
    }

    /**
     * Removes the footer from the HTML document.
     */
    public function removeFooter(){
        $this->footer = "";
    }

    /**
     * Set HTML tag attributes for the document.
     *
     * @param string $attr The HTML tag attributes to be added.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function addHtmlTagAttributes(string $attr) {
        $this->htmlAttr[] = $attr;
    }

    /**
     * Set body tag attributes for the document.
     *
     * @param string $attr The body tag attributes to be added.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function addBodyTagAttributes(string $attr) {
        $this->bodyAttr[] = $attr;
    }

    /**
     * Applies a filter to the specified HTML tag attribute.
     *
     * @param string $htmlFilter The HTML tag to filter (e.g., 'html', 'body', 'lang', 'title', 'charset').
     * @param \Closure $function The filter function to apply.
     * @return mixed The filtered attribute value.
     */
    public function html_filter(string $htmlFilter, \Closure $function) {
        $accepted = ['html', 'body', 'lang', 'title', 'charset'];
        $htmlFilter = strtolower($htmlFilter);

        if (!in_array($htmlFilter, $accepted)) {
            trigger_error("Invalid HTML tag: $htmlFilter", E_USER_WARNING);
            return null;
        }

        if($htmlFilter == 'title'){
            $filter = call_user_func($function, $this->title);
            return $this->title = $filter;
        } else {
            $filter = call_user_func($function, $this->{$htmlFilter . 'Attr'});
            return $this->{$htmlFilter . 'Attr'} = $filter;
        }
    }

    /**
     * Set the lang attribute for the document.
     * 
     * @param string $lang The lang attribute of the document.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function setLang(string $lang) {
        $this->langAttr = $lang;
    }

    /**
     * Get the lang attribute of the document.
     * 
     * @return string The lang attribute of the document.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function getLang():string {
        return $this->langAttr;
    }

    /**
     * Set the charset for the document.
     * 
     * @param string $charset The charset attribute of the document.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function setCharset(string $charset) {
        $this->charsetAttr = $charset;
    }

    /**
     * Add a CDN link to the stored resources.
     *
     * @param string $type The type of the CDN link (e.g., 'link', 'script', etc.).
     * @param string $attr The attribute information for the CDN link.
     */
    public function addCDN(string $type, string $attr) {
        $validTypes = ['link', 'script'];
        $type = strtolower($type);
    
        if (!in_array($type, $validTypes)) {
            trigger_error("Invalid CDN type: $type", E_USER_WARNING);
        }
    
        $this->cdns[] = [$type => $attr];
    }

    /**
     * Adds a meta tag to the HTML document.
     *
     * @param string $attrs The attributes of the meta tag.
     */
    public function addMeta(string $attrs) {
        $this->metas[] = $attrs;
    }

    /**
     * Sets the Ajax URL for internal use and makes it available in JavaScript.
     *
     * Constructs the Ajax URL to be used internally, and the resulting URL is made
     * accessible in JavaScript.
     *
     * @param string $var The name of the JavaScript variable to store the Ajax URL. Default value is "ajax_url".
     */
    public function setAjaxUrl(string $var = "ajax_url"){
        $this->ajaxVar = $var;
        $this->ajaxUrl = $this->url("app/admin/cml-ajax.php");
    }

    /**
     * Register a hook to place content at a specific location in the HTML document.
     *
     * @param string   $hookName      The name of the hook (e.g., 'before_head', 'after_head', 'top_body', etc.).
     * @param mixed    $contentSource The file path, a callable function, or HTML code to provide content.
     * @param int      $level         The priority level for rendering the content (higher levels are rendered first).
     */
    public function addHook(string $hookName, $contentSource, int $level = 0) {
        if(!in_array($hookName, $this->regHooks)){
            trigger_error("Invalid hook name: $hookName", E_USER_WARNING);
        }
        $this->hooks[$hookName][] = [
            'source' => $contentSource,
            'level' => $level,
        ];
    }

    /**
     * Adds a stylesheet link to the HTML document.
     *
     * @param string $href The path to the stylesheet.
     * @param string|array $attributes Additional attributes for the link element (optional).
     * @param bool $fromRoot Whether the path is relative to the document root.
     */
    public function addStyle(string $href, $attributes = "", bool $fromRoot = false) {
        if ($href) $this->_addResource($href, $this->styles, $attributes, $fromRoot);
    }

    /**
     * Adds a script link to the HTML document.
     *
     * @param string $src The path to the script.
     * @param string|array $attributes Additional attributes for the script element (optional).
     * @param bool $fromRoot Whether the path is relative to the document root.
     */
    public function addScript(string $src, $attributes = "", bool $fromRoot = false) {
        if ($src) $this->_addResource($src, $this->scripts, $attributes, $fromRoot);
    }

    /**
     * Retrieves the path of a module file based on the module name and extension.
     *
     * @param string $moduleName The name of the module.
     * @param string $extension The file extension to search for (default: 'min.js').
     * @param bool $autoAdd Determines whether to automatically add the module file to the HTML document (default: true).
     * @param string $attributes Additional attributes to add to the HTML tag (default: empty string).
     * @return string The path of the module file.
     */
    public function node_module(string $moduleName, string $extension = 'min.js', bool $autoAdd = true, $attributes = ""): string {
        $lowercaseModuleName = strtolower($moduleName);
        $moduleDir = self::getRootPath('/node_modules/' . $lowercaseModuleName);
    
        if (is_dir($moduleDir)) {
            $files = $this->_recursiveFileSearch($moduleDir, $extension);
            if (!empty($files)) {
                $linkPath = str_replace(self::getRootPath(), '', $files[0]);
                $extension = strtolower(pathinfo(".$extension", PATHINFO_EXTENSION));
                switch ($extension) {
                    case 'css':
                        if ($autoAdd) {
                            $this->addStyle($linkPath, $attributes, true);
                        }
                        break;
                    case 'js':
                        if ($autoAdd) {
                            $this->addScript($linkPath, $attributes, true);
                        }
                        break;
                }
                return $linkPath;
            } else {
                trigger_error("No file with extension '$extension' found for module '$moduleName'.", E_USER_ERROR);
            }
        } else {
            trigger_error("Module '$moduleName' not found.", E_USER_ERROR);
        }
    }

    /**
     * Recursively searches for files with a specific extension in a directory.
     *
     * @param string $dir The directory to search in.
     * @param string $extension The file extension to search for.
     * @return array An array of file paths matching the specified extension.
     */
    protected function _recursiveFileSearch(string $dir, string $extension): array {
        $files = glob($dir . "/*.$extension");
        foreach (glob($dir . '/*', GLOB_ONLYDIR) as $subdir) {
            $files = array_merge($files, $this->_recursiveFileSearch($subdir, $extension));
        }
        return $files;
    }

    /**
     * Renders a specified component with optional variables and includes it in the output.
     *
     * @param string $component The name of the component to be rendered.
     * @param array $variables An associative array of variables to be extracted and made available within the component.
     */
    public function component(string $component, array $variables = []) {
        $component = str_replace(".php", '', $component).".php";
        $path = self::getRootPath(COMPONENTS_PATH.$component);
        
        if (file_exists($path)) {
            extract($variables);
            ob_start();
            require $path;
            return $this->minifyHTML(ob_get_clean());
        } else {
            trigger_error(htmlentities("Component $component | not found in ".$path), E_USER_WARNING);
        }
    }

    /**
     * Generic function to add content (header or footer) to the HTML document.
     *
     * @param string $path The path to the content file.
     * @param string|array $contentOrVariable The content to add or variables if array.
     * @param string &$property The property to store the content in.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    protected function _addContent(string $path, $contentOrVariable, string &$property, array $variables = []) {
        $contentFile = $path ?? '';
        if (empty($contentFile) && empty($contentOrVariable)) {
            return trigger_error("Could not set the $path", E_USER_ERROR);
        }

        if(is_array($contentOrVariable)) {
            extract($contentOrVariable);
            goto a;
        }

        if (!empty($contentOrVariable)) {
            $property = $contentOrVariable;
        } else {
            a:
            if (file_exists(self::getRootPath($contentFile))) {
                extract($variables);
                ob_start();
                require self::getRootPath($contentFile);
                $property = ob_get_clean();
            } else {
                trigger_error("$path file does not exist: $contentFile", E_USER_WARNING);
            }
        }
    }

    /**
     * Converts an associative array to HTML attribute string.
     *
     * @param array $attributes
     * @return string
     */
    protected function _arrToHtmlAttrs(array $attributes): string {
        $htmlAttributes = '';
        foreach ($attributes as $key => $value) {
            $htmlAttributes .= " $key=\"$value\"";
        }
        return $htmlAttributes;
    }
    
    /**
     * Adds a resource link to the HTML document.
     *
     * @param string $path The path to the resource.
     * @param array &$container The container (styles or scripts) to which the resource should be added.
     * @param string|array $attributes Additional attributes for the HTML element (e.g., 'media="screen"', 'async', 'defer', etc.).
     * @param bool $fromRoot Whether the path is relative to the document root.
     */
    protected function _addResource(string $path, array &$container, $attributes = "", bool $fromRoot = false) {
        $const = $container === $this->styles ? 'STYLE_PATH' : 'SCRIPT_PATH';

        $fullPath = $fromRoot ? $path : (constant($const) ?? '') . $path;

        if (!file_exists(self::getRootPath($fullPath))) {
            $resourceType = $container === $this->styles ? 'stylesheet' : 'script';
            return trigger_error("Could not find $resourceType file => '" . htmlentities($fullPath) . "'", E_USER_WARNING);
        }

        if (!is_array($attributes)) {
            $attributes = !empty($attributes) ? " $attributes" : "";
        } else {
            $attributes = $this->_arrToHtmlAttrs($attributes);
        }

        if (filesize(self::getRootPath($fullPath)) !== 0) {
            $container[] = '"' . self::url($fullPath) . '"' . $attributes;
        }
    }

    /**
     * Compresses CSS or JavaScript by removing whitespace and comments.
     *
     * @param string $path The path to the CSS or JavaScript file to compress.
     * @param string $configPath The config path for the file.
     * @param string $fileExtension The file extension to use for the compressed file.
     * @return string The path to the compressed file.
     */
    protected static function _compressFile(string $path, string $configPath, string $fileExtension): string {
        $newFileName = str_replace($fileExtension, ".min{$fileExtension}", $path);
        $filePath = self::getRootPath($configPath ? $configPath . $path : $path);
        $compressDir = "_min/";

        if (!is_readable($filePath)) {
            return trigger_error(htmlentities($filePath) . " - File does not exist or is not readable", E_USER_ERROR);
        }

        $fileContent = file_get_contents($filePath);

        if ($fileContent === false || $fileContent === '') {
            return '';
        }

        $fileContent = preg_replace(
            ['/\/\/[^\n\r]*/', '/\/\*[\s\S]*?\*\//', '/\s*([{}:;,=()])\s*/', '/;\s*}/', '/\s+/'],
            ['', '', '$1', '}', ' '],
            $fileContent
        );

        $compressedPath = self::getRootPath($configPath) . $compressDir;

        if (!file_exists($compressedPath)) {
            mkdir($compressedPath);
        }

        $compressedFilePath = $compressedPath . $newFileName;

        if (file_exists($compressedFilePath) && file_get_contents($compressedFilePath) === $fileContent) {
            return $compressDir.$newFileName;
        }

        file_put_contents($compressedFilePath, $fileContent);

        return $compressDir.$newFileName;
    }

    /**
     * Compresses CSS or JavaScript by removing whitespace and comments.
     *
     * @param string $path The path to the CSS or JavaScript file to compress.
     * @return string The path to the compressed file.
     */
    public static function compress(string $path):string {
        $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
        
        if ($fileExtension === 'css') {
            return self::_compressFile($path, STYLE_PATH ?? '', '.css');
        } elseif ($fileExtension === 'js') {
            return self::_compressFile($path, SCRIPT_PATH ?? '', '.js');
        } else {
            return $path;
        }
    }

    /**
     * Get the content for a specific hook and return it.
     *
     * @param string $hookName The name of the hook (e.g., 'before_head', 'after_head', 'top_body', etc.).
     * @return string The content for the specified hook.
     */
    protected function _getHookContent(string $hookName) {
        if (isset($this->hooks[$hookName])) {
            $hooks = $this->hooks[$hookName];
            $this->_sortByKey($hooks, "level");

            foreach ($hooks as $hook) {
                $contentSource = $hook['source'];
                
                if (is_callable($contentSource)) {
                    $content = call_user_func($contentSource);
                    echo is_string($content) ? $content : '';
                } elseif (file_exists(self::getRootPath($contentSource))) {
                    ob_start();
                    require self::getRootPath($contentSource);
                    echo ob_get_clean();
                } elseif (is_string($contentSource)) {
                    echo $contentSource;
                } else {
                    trigger_error("Invalid content source for the hook: $hookName", E_USER_WARNING);
                }
            }
        }
        return '';
    }

    /**
     * Sorts an array of associative arrays based on a specified key.
     *
     * @param array $array The array to be sorted (passed by reference).
     * @param string $key The key by which the array should be sorted.
     */
    protected function _sortByKey(array &$array, $key) {
        usort($array, function($a, $b) use ($key) {
            return $b[$key] - $a[$key];
        });
    }

    /**
     * Minifies HTML content by removing unnecessary spaces, line breaks, tabs, and HTML comments.
     *
     * This function takes an HTML string as input, applies various regular expressions
     * to remove extra whitespace, HTML comments, and spaces around HTML tags, and returns
     * the minified HTML content.
     *
     * @param string $html The HTML content to be minified.
     *
     * @return string The minified HTML content without unnecessary spaces and comments.
     */
    public function minifyHTML(string $html):string {
        if ($this->minifyHTML === true) {
            // Remove spaces, line breaks, and tabs
            $minified = preg_replace('/\s+/', ' ', $html);
            // Remove HTML comments
            $minified = preg_replace('/<!--(.|\s)*?-->/', '', $minified);
            // Remove unnecessary spaces around tags
            return preg_replace('/>\s+</', '><', $minified);
        } else {
            return $html;
        }
    }

    /**
     * Builds the complete HTML structure.
     */
    public function build() {
        if($this->builded === false){
            $this->builded = true;
            ob_start();
            $this->_buildHtmlStart();
            $this->_buildHead();
            $this->_buildBody();
            echo $this->minifyHTML(preg_replace('/\h*<([^>]*)>\h*/', '<$1>', ob_get_clean()));
        }
    }
    
    /**
     * Builds the opening HTML tags and outputs any content hooks before the head.
     */
    protected function _buildHtmlStart() {
        $attr = $this->_arrToHtmlAttrs($this->htmlAttr);
        ?>
        <!DOCTYPE html>
        <html lang="<?= $this->langAttr ?>"<?= $attr?>>
        <?= $this->_getHookContent(self::BEFORE_HEAD); ?>
        <?php
    }
    
    /**
     * Builds the head section of the HTML document with meta tags, title, scripts, and styles.
     */
    protected function _buildHead() {
        ?>
        <head>
            <?= $this->_getHookContent(self::TOP_HEAD); ?>
            <meta charset="<?= $this->charsetAttr ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?php $this->_buildMetas(); ?>
            <title><?= empty($this->title) ? APP_NAME : $this->title?></title>
            <?= !empty($this->ajaxUrl) ? "<script>let {$this->ajaxVar} = '{$this->ajaxUrl}'</script>" : ''?>
            <link rel="icon" type="image/x-icon" href="<?= self::url($this->favicon) ?>">
            <?php $this->_buildCdns(); ?>
            <?php $this->_buildStyles(); ?>
            <?php $this->_buildScripts(); ?>
            <?= $this->_getHookContent(self::BOTTOM_HEAD); ?>
        </head>
        <?= $this->_getHookContent(self::AFTER_HEAD); ?>
        <?php
    }

    /**
     * Builds the body section of the HTML document with hooks, header content, etc.
     */   
    protected function _buildBody() {
        $attr = $this->_arrToHtmlAttrs($this->bodyAttr);
        echo $this->_getHookContent(self::BEFORE_BODY);
        echo "<body{$attr}>";
        echo $this->_getHookContent(self::TOP_BODY); 
        echo $this->header;
    }
    
    /**
     * Builds meta tags in the head section based on the provided array of meta attributes.
     */
    protected function _buildMetas() {
        foreach ($this->metas as $meta): ?>
            <meta <?= $meta ?>>
        <?php endforeach;
    }
    
    /**
     * Builds content delivery network (CDN) links based on the provided array of CDNs.
     */
    protected function _buildCdns() {
        foreach ($this->cdns as $cdns): ?>
            <?php foreach ($cdns as $tag => $attributes): ?>
                <<?= $tag ?> <?= $attributes ?>>
                <?php if ($tag == "script"): ?>
                    </<?= $tag ?>>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach;
    }
    
    /**
     * Builds stylesheet links in the head section based on the provided array of styles.
     */
    protected function _buildStyles() {
        foreach ($this->styles as $style): ?>
            <link rel="stylesheet" href=<?= $style ?>>
        <?php endforeach;
    }
  
    /**
     * Builds script tags in the head or body section based on the provided array of scripts.
     */
    protected function _buildScripts() {
        foreach ($this->scripts as $script): ?>
            <script src=<?= $script ?>></script>
        <?php endforeach;
    }

    /**
     * Close the application correctly.
     */
    protected function _build_end() {
        ob_start();
        echo $this->minifyHTML($this->_getHookContent(self::BEFORE_BODY));
        echo $this->footer;
        if ($this->builded) {
            echo PHP_EOL.'</body>';
        }
        echo $this->_getHookContent(self::AFTER_BODY);
        if ($this->builded) {
            echo PHP_EOL.'</html>';
        }
        echo $this->minifyHTML(ob_get_clean());
        exit;
    }
}