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
 * @see https://docs.callmeleon.de/html
 */
abstract class HTMLBuilder extends Cache
{
    use Functions\Functions;

    /**
     * Represents a position before the `<head>` tag in an HTML document.
     */
    const BEFORE_HEAD = 'before_head';

    /**
     * Represents a position at the top of the `<head>` tag in an HTML document.
     */
    const TOP_HEAD = 'top_head';

    /**
     * Represents a position at the bottom of the `<head>` tag in an HTML document.
     */
    const BOTTOM_HEAD = 'bottom_head';

    /**
     * Represents a position after the `<head>` tag in an HTML document.
     */
    const AFTER_HEAD = 'after_head';

    /**
     * Represents a position before the `<body>` tag in an HTML document.
     */
    const BEFORE_BODY = 'before_body';

    /**
     * Represents a position at the top of the `<body>` tag in an HTML document.
     */
    const TOP_BODY = 'top_body';

    /**
     * Represents a position at the bottom of the `<body>` tag in an HTML document.
     */
    const BOTTOM_BODY = 'bottom_body';

    /**
     * Represents a position after the `<body>` tag in an HTML document.
     */
    const AFTER_BODY = 'after_body';

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
     * @var string Stores the currently url.
     */
    public string $currentUrl;
    /**
     * 
     * @var string Stores the currently name of route.
     */
    public string $currentRouteName;

    /**
     * @var array Store inline styles for HTML elements.
     */
    protected $inlineStyles = [];

    /**
     * @var array Store inline scripts for HTML elements.
     */
    protected $inlineScripts = [];

    /**
     * @var array Store custom bar elements for HTML elements.
     */
    private array $customBarElements = [];

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
    public function activateMinifyHTML()
    {
        $this->minifyHTML = true;
    }

    /**
     * Sets the project name and updates the title accordingly.
     *
     * @param string $projectName The project name.
     */
    public function setProjectName(string $projectName)
    {
        $this->projectName = $projectName;
        $this->setTitle($this->projectName);
    }

    /**
     * Sets the title of the HTML document.
     *
     * @param string $title The title of the HTML document.
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Sets the path to the favicon.
     *
     * @param string $favicon The path to the favicon.
     */
    public function setFavicon(string $favicon)
    {
        $this->favicon = $favicon;
    }

    /**
     * Adds a header element to the HTML document.
     *
     * @param string|array $header The header element to add  or variables if array.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    public function addHeader($header = '', array $variables = [])
    {
        $this->_addContent(cml_config('COMPONENTS_PATH') . 'header.php', $header, $this->header, $variables);
    }

    /**
     * Removes the header from the HTML document.
     */
    public function removeHeader()
    {
        $this->header = "";
    }

    /**
     * Adds a footer element to the HTML document.
     *
     * @param string|array $footer The footer element to add  or variables if array.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    public function addFooter($footer = '', array $variables = [])
    {
        $this->_addContent(cml_config('COMPONENTS_PATH') . 'footer.php', $footer, $this->footer, $variables);
    }

    /**
     * Removes the footer from the HTML document.
     */
    public function removeFooter()
    {
        $this->footer = "";
    }

    /**
     * Returns all registered hooks.
     *
     * @return array The array of registered hooks.
     */
    public function getAllHooks()
    {
        return $this->regHooks;
    }

    /**
     * Set HTML tag attributes for the document.
     *
     * @param string $attr The HTML tag attributes to be added.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function addHtmlTagAttributes(string $attr)
    {
        $this->htmlAttr[] = $attr;
    }

    /**
     * Set body tag attributes for the document.
     *
     * @param string $attr The body tag attributes to be added.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function addBodyTagAttributes(string $attr)
    {
        $this->bodyAttr[] = $attr;
    }

    /**
     * Applies a filter to the specified HTML tag attribute.
     *
     * @param string $htmlFilter The HTML tag to filter (e.g., 'html', 'body', 'lang', 'title', 'charset').
     * @param \Closure $function The filter function to apply.
     * @return mixed The filtered attribute value.
     */
    public function html_filter(string $htmlFilter, \Closure $function)
    {
        $accepted = ['html', 'body', 'lang', 'title', 'charset'];
        $htmlFilter = strtolower($htmlFilter);

        if (!in_array($htmlFilter, $accepted)) {
            trigger_error("Invalid HTML tag: $htmlFilter", E_USER_WARNING);
            return null;
        }

        if ($htmlFilter == 'title') {
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
    public function setLang(string $lang)
    {
        $this->langAttr = $lang;
    }

    /**
     * Get the lang attribute of the document.
     * 
     * @return string The lang attribute of the document.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function getLang(): string
    {
        return $this->langAttr;
    }

    /**
     * Set the charset for the document.
     * 
     * @param string $charset The charset attribute of the document.
     * @deprecated since version 2.8, to be removed in 3.0. Use html_filter() instead.
     */
    public function setCharset(string $charset)
    {
        $this->charsetAttr = $charset;
    }

    /**
     * Add a CDN link to the stored resources.
     *
     * @param string $type The type of the CDN link (e.g., 'link', 'script', etc.).
     * @param string $attr The attribute information for the CDN link.
     */
    public function addCDN(string $type, string $attr)
    {
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
    public function addMeta(string $attrs)
    {
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
    public function setAjaxUrl(string $var = "ajax_url")
    {
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
    public function addHook(string $hookName, $contentSource, int $level = 0)
    {
        $this->hooks[$hookName][] = [
            'source' => $contentSource,
            'level' => $level,
        ];
    }

    /**
     * Adds a custom hook name to the list of registered hooks and echoes the hook content.
     *
     * @param string $customHookName The name of the custom hook.
     */
    public function setHook(string $customHookName)
    {
        $trace = debug_backtrace();
        foreach ($trace as $caller) {
            if (isset($caller['function']) && $caller['function'] === 'setHook' && isset($caller['file'])) {
                $file = str_replace(rtrim(self::getRootPath(), "/"), '', $caller['file']);
                $line = $caller['line'];
            }
        }

        $this->regHooks['user_hooks'][] = ['name' => $customHookName, 'file' => $file, 'line' => $line];
        echo $this->_getHookContent($customHookName);
    }

    /**
     * Adds a stylesheet link to the HTML document.
     *
     * @param string $href The path to the stylesheet.
     * @param string|array $attributes Additional attributes for the link element (optional).
     * @param bool $fromRoot Whether the path is relative to the document root.
     */
    public function addStyle(string $href, $attributes = "", bool $fromRoot = false)
    {
        if ($href) $this->_addResource($href, $this->styles, $attributes, $fromRoot);
    }

    /**
     * Adds a script link to the HTML document.
     *
     * @param string $src The path to the script.
     * @param string|array $attributes Additional attributes for the script element (optional).
     * @param bool $fromRoot Whether the path is relative to the document root.
     */
    public function addScript(string $src, $attributes = "", bool $fromRoot = false)
    {
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
    public function node_module(string $moduleName, string $extension = 'min.js', bool $autoAdd = true, $attributes = ""): string
    {
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
    protected function _recursiveFileSearch(string $dir, string $extension): array
    {
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
    public function component(string $component, array $variables = [])
    {
        $component = str_replace(".php", '', $component) . ".php";
        $path = self::getRootPath(cml_config('COMPONENTS_PATH') . $component);

        if (file_exists($path)) {
            extract($variables);
            ob_start();
            require $path;
            return $this->minifyHTML(ob_get_clean());
        } else {
            trigger_error(htmlentities("Component $component | not found in " . $path), E_USER_ERROR);
        }
    }

    /**
     * Adds a component to a hook.
     *
     * @param string $hookName The name of the hook.
     * @param string $component The name of the component to add.
     * @param array $variables An array of variables to pass to the component.
     * @param int $level The nesting level of the component.
     */
    public function componentHook(string $hookName, string $component, array $variables = [], int $level = 0)
    {
        $this->addHook($hookName, $this->component($component, $variables, $level));
    }

    /**
     * Generic function to add content (header or footer) to the HTML document.
     *
     * @param string $path The path to the content file.
     * @param string|array $contentOrVariable The content to add or variables if array.
     * @param string &$property The property to store the content in.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    protected function _addContent(string $path, $contentOrVariable, string &$property, array $variables = [])
    {
        $contentFile = $path ?? '';
        if (empty($contentFile) && empty($contentOrVariable)) {
            return trigger_error("Could not set the $path", E_USER_ERROR);
        }

        if (is_array($contentOrVariable)) {
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
    protected function _arrToHtmlAttrs(array $attributes): string
    {
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
    protected function _addResource(string $path, array &$container, $attributes = "", bool $fromRoot = false)
    {
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
    protected static function _compressFile(string $path, string $configPath, string $fileExtension): string
    {
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
            return $compressDir . $newFileName;
        }

        file_put_contents($compressedFilePath, $fileContent);

        return $compressDir . $newFileName;
    }

    /**
     * Compresses CSS or JavaScript by removing whitespace and comments.
     *
     * @param string $path The path to the CSS or JavaScript file to compress.
     * @return string The path to the compressed file.
     */
    public static function compress(string $path): string
    {
        $fileExtension = pathinfo($path, PATHINFO_EXTENSION);

        if ($fileExtension === 'css') {
            return self::_compressFile($path, cml_config('STYLE_PATH') ?? '', '.css');
        } elseif ($fileExtension === 'js') {
            return self::_compressFile($path, cml_config('SCRIPT_PATH') ?? '', '.js');
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
    protected function _getHookContent(string $hookName)
    {
        if (isset($this->hooks[$hookName])) {
            $hooks = $this->hooks[$hookName];
            $this->_sortByKey($hooks, "level");

            foreach ($hooks as $hook) {
                $contentSource = $hook['source'];

                if (is_callable($contentSource)) {
                    $content = call_user_func($contentSource);
                    echo is_string($content) ? $content : '';
                } elseif (is_string($contentSource)) {
                    echo $contentSource;
                } elseif (file_exists(self::getRootPath($contentSource))) {
                    ob_start();
                    require self::getRootPath($contentSource);
                    echo ob_get_clean();
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
    protected function _sortByKey(array &$array, $key)
    {
        usort($array, function ($a, $b) use ($key) {
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
    public function minifyHTML(string $html): string
    {
        if ($this->minifyHTML === true) {
            // Replace temporary <pre> tags
            $html = preg_replace_callback('/<pre\b[^>]*>(.*?)<\/pre>/is', function ($matches) {
                return '<pre>' . base64_encode($matches[1]) . '</pre>';
            }, $html);

            // Remove spaces, line breaks, and tabs outside of <pre> tags
            $minified = preg_replace('/\s+/', ' ', $html);

            // Remove HTML comments
            $minified = preg_replace('/<!--(.|\s)*?-->/', '', $minified);

            // Remove unnecessary spaces around tags
            $minified = preg_replace('/>\s+</', '><', $minified);

            // Wiederherstellen der <pre> Tag Inhalte
            $minified = preg_replace_callback('/<pre>(.*?)<\/pre>/is', function ($matches) {
                return '<pre>' . base64_decode($matches[1]) . '</pre>';
            }, $minified);

            return $minified;
        } else {
            return $html;
        }
    }

    /**
     * Checks if the cache is enabled and retrieves the cached content if available.
     *
     * @param string $cacheKey The key used to identify the cached content.
     */
    public function checkCache(string $cacheKey)
    {
        if ($this->cacheEnabled === true) {
            $cachedContent = $this->getCache($cacheKey);
            if ($cachedContent !== false && cml_config('PRODUCTION') !== false) {
                echo $cachedContent;
                exit;
            }
        }
    }

    /**
     * Sets the robots meta tag based on the indexing status of the current route
     */
    protected function setRobotsMetaTag()
    {
        if ($this instanceof Router && method_exists($this, 'isIndexable')) {
            $indexable = $this->isIndexable($this->currentUrl);
            $content = $indexable ? 'index, follow' : 'noindex, nofollow';
            $this->addMeta('name="robots" content="' . $content . '"');
        }
    }

    /**
     * Add inline CSS style
     *
     * @param string $style
     */
    public function addInlineStyle(string $style)
    {
        $this->inlineStyles[] = $style;
    }

    /**
     * Add inline JavaScript
     *
     * @param string $script
     */
    public function addInlineScript(string $script)
    {
        $this->inlineScripts[] = $script;
    }

    /**
     * Build inline styles
     */
    protected function _buildInlineStyles()
    {
        if (!empty($this->inlineStyles)) {
            echo "<style>\n" . implode("\n", $this->inlineStyles) . "\n</style>\n";
        }
    }

    /**
     * Build inline scripts
     */
    protected function _buildInlineScripts()
    {
        if (!empty($this->inlineScripts)) {
            echo "<script>\n" . implode("\n", $this->inlineScripts) . "\n</script>\n";
        }
    }

    /**
     * Builds the complete HTML structure.
     */
    protected function buildHTML(string $outputContent = "")
    {
        $this->setRobotsMetaTag();
        $cacheKey = $this->currentUrl;
        $this->checkCache($cacheKey);

        $attrHTML = $this->_arrToHtmlAttrs($this->htmlAttr);
        $attrBody = $this->_arrToHtmlAttrs($this->bodyAttr);

        ob_start();
?>
        <!DOCTYPE html>
        <html lang="<?= $this->langAttr ?>" <?= $attrHTML ?>>
        <?= $this->_getHookContent(self::BEFORE_HEAD); ?>

        <head>
            <?= $this->_getHookContent(self::TOP_HEAD); ?>
            <meta charset="<?= $this->charsetAttr ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?php $this->_buildMetas(); ?>
            <title><?= empty($this->title) ? cml_config('APP_NAME') : $this->title ?></title>
            <?= !empty($this->ajaxUrl) ? "<script>let {$this->ajaxVar} = '{$this->ajaxUrl}'</script>" : '' ?>
            <link rel="icon" type="image/x-icon" href="<?= self::url($this->favicon) ?>">
            <?php $this->_styleInfoBar() ?>
            <?php $this->_buildCdns(); ?>
            <?php $this->_buildStyles(); ?>
            <?php $this->_buildInlineStyles(); ?>
            <?php $this->_buildScripts(); ?>
            <?php $this->_buildInlineScripts(); ?>
            <?= $this->_getHookContent(self::BOTTOM_HEAD); ?>
        </head>
        <?= $this->_getHookContent(self::AFTER_HEAD); ?>
        <?= $this->_getHookContent(self::BEFORE_BODY); ?>
        <?= "<body{$attrBody}>"; ?>
        <?= $this->_getHookContent(self::TOP_BODY); ?>
        <?= $this->header; ?>
        <?= $this->minifyHTML($outputContent) ?>
        <?= $this->_getHookContent(self::BEFORE_BODY); ?>
        <?= $this->footer; ?>
        <?= PHP_EOL . '</body>'; ?>
        <?php $this->_scriptInfoBar() ?>
        <?= $this->_getHookContent(self::AFTER_BODY); ?>
        <?= PHP_EOL . '</html>'; ?>
        <?php
        $htmlContent = $this->minifyHTML(preg_replace('/\h+(?=<)/', ' ', ob_get_clean()));

        if ($this->cacheEnabled === true) {
            $this->setCache($cacheKey, $htmlContent);
        }

        echo $htmlContent;

        if (cml_config('CML_DEBUG_BAR') === true && cml_config('PRODUCTION') === false) {
            $this->renderInfoBar();
        }
        exit;
    }

    /**
     * Adds a bar element to the HTMLBuilder.
     *
     * @param string $text The text content of the bar element.
     * @param string $hoverContent The hover content to be displayed when the bar element is hovered (optional).
     * @param string $customClass The custom CSS class to be applied to the bar element (optional).
     * @return void
     */
    public function addBarElement(string $text, string $hoverContent = "", string $customClass = "")
    {
        ob_start();
        ?>
        <div class="<?= ($hoverContent ? 'info-item ' : '') . $customClass ?>">
            <?= $text ?>
            <?php if ($hoverContent) : ?>
                <div class="infoBox">
                    <?= $hoverContent ?>
                </div>
            <?php endif; ?>
        </div>
    <?php
        $customElement = ob_get_clean();
        $this->customBarElements[] = $customElement;
    }


    public function renderInfoBar()
    {
        global $cml_script_start, $cml_db_request_amount, $cml_db_request_query, $cml_used_controller;
        $execution_time = microtime(true) - $cml_script_start;
        $cml_execution_time = round($execution_time < 1 ? $execution_time * 1000 : $execution_time, $execution_time < 1 ? 0 : 2) . ($execution_time < 1 ? ' ms' : ' s');
        $type = strpos($this->currentRouteName, '/') !== false ? '' : '@';
        $httpResponseRange = (string) http_response_code();
        ob_start();
    ?>
        <div id="cmlInfoBar">
            <div class="cmlBarBegin">
                <div><?= $_SERVER['REQUEST_METHOD'] ?></div>
                <div class="statusCode_<?= $httpResponseRange[0] ?>00"><?= http_response_code() ?></div>
                <div class="info-item">
                    <span class="cmlNameRoute__type"><?= $type ?></span>
                    <span><?= $this->currentRouteName ?></span>
                    <div class="infoBox">
                        <table>
                            <tr>
                                <td>Used Controller</td>
                                <td>
                                    <?php foreach ($cml_used_controller as $item) : ?>
                                        <?= $item['controller'] ?>::<?= $item['method'] ?> <br>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Session Active</td>
                                <td><?= (useTrait()->isSessionStarted()) ? "Yes" : "No" ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div><?= $cml_execution_time ?></div>
                <div class="cmlDBRequest info-item">
                    <svg fill="#6f6f6f" height="25px" width="20px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 55 55" xml:space="preserve">
                        <path d="M52.354,8.51C51.196,4.22,42.577,0,27.5,0C12.423,0,3.803,4.22,2.646,8.51C2.562,8.657,2.5,8.818,2.5,9v0.5V21v0.5V22v11v0.5V34v12c0,0.162,0.043,0.315,0.117,0.451C3.798,51.346,14.364,55,27.5,55c13.106,0,23.655-3.639,24.875-8.516C52.455,46.341,52.5,46.176,52.5,46V34v-0.5V33V22v-0.5V21V9.5V9C52.5,8.818,52.438,8.657,52.354,8.51z M50.421,33.985c-0.028,0.121-0.067,0.241-0.116,0.363c-0.04,0.099-0.089,0.198-0.143,0.297c-0.067,0.123-0.142,0.246-0.231,0.369c-0.066,0.093-0.141,0.185-0.219,0.277c-0.111,0.131-0.229,0.262-0.363,0.392c-0.081,0.079-0.17,0.157-0.26,0.236c-0.164,0.143-0.335,0.285-0.526,0.426c-0.082,0.061-0.17,0.12-0.257,0.18c-0.226,0.156-0.462,0.311-0.721,0.463c-0.068,0.041-0.141,0.08-0.212,0.12c-0.298,0.168-0.609,0.335-0.945,0.497c-0.043,0.021-0.088,0.041-0.132,0.061c-0.375,0.177-0.767,0.351-1.186,0.519c-0.012,0.005-0.024,0.009-0.036,0.014c-2.271,0.907-5.176,1.67-8.561,2.17c-0.017,0.002-0.034,0.004-0.051,0.007c-0.658,0.097-1.333,0.183-2.026,0.259c-0.113,0.012-0.232,0.02-0.346,0.032c-0.605,0.063-1.217,0.121-1.847,0.167c-0.288,0.021-0.59,0.031-0.883,0.049c-0.474,0.028-0.943,0.059-1.429,0.076C29.137,40.984,28.327,41,27.5,41s-1.637-0.016-2.432-0.044c-0.486-0.017-0.955-0.049-1.429-0.076c-0.293-0.017-0.595-0.028-0.883-0.049c-0.63-0.046-1.242-0.104-1.847-0.167c-0.114-0.012-0.233-0.02-0.346-0.032c-0.693-0.076-1.368-0.163-2.026-0.259c-0.017-0.002-0.034-0.004-0.051-0.007c-3.385-0.5-6.29-1.263-8.561-2.17c-0.012-0.004-0.024-0.009-0.036-0.014c-0.419-0.168-0.812-0.342-1.186-0.519c-0.043-0.021-0.089-0.041-0.132-0.061c-0.336-0.162-0.647-0.328-0.945-0.497c-0.07-0.04-0.144-0.079-0.212-0.12c-0.259-0.152-0.495-0.307-0.721-0.463c-0.086-0.06-0.175-0.119-0.257-0.18c-0.191-0.141-0.362-0.283-0.526-0.426c-0.089-0.078-0.179-0.156-0.26-0.236c-0.134-0.13-0.252-0.26-0.363-0.392c-0.078-0.092-0.153-0.184-0.219-0.277c-0.088-0.123-0.163-0.246-0.231-0.369c-0.054-0.099-0.102-0.198-0.143-0.297c-0.049-0.121-0.088-0.242-0.116-0.363C4.541,33.823,4.5,33.661,4.5,33.5c0-0.113,0.013-0.226,0.031-0.338c0.025-0.151,0.011-0.302-0.031-0.445v-7.424c0.028,0.026,0.063,0.051,0.092,0.077c0.218,0.192,0.44,0.383,0.69,0.567C9.049,28.786,16.582,31,27.5,31c10.872,0,18.386-2.196,22.169-5.028c0.302-0.22,0.574-0.447,0.83-0.678l0.001-0.001v7.424c-0.042,0.143-0.056,0.294-0.031,0.445c0.019,0.112,0.031,0.225,0.031,0.338C50.5,33.661,50.459,33.823,50.421,33.985z M50.5,13.293v7.424c-0.042,0.143-0.056,0.294-0.031,0.445c0.019,0.112,0.031,0.225,0.031,0.338c0,0.161-0.041,0.323-0.079,0.485c-0.028,0.121-0.067,0.241-0.116,0.363c-0.04,0.099-0.089,0.198-0.143,0.297c-0.067,0.123-0.142,0.246-0.231,0.369c-0.066,0.093-0.141,0.185-0.219,0.277c-0.111,0.131-0.229,0.262-0.363,0.392c-0.081,0.079-0.17,0.157-0.26,0.236c-0.164,0.143-0.335,0.285-0.526,0.426c-0.082,0.061-0.17,0.12-0.257,0.18c-0.226,0.156-0.462,0.311-0.721,0.463c-0.068,0.041-0.141,0.08-0.212,0.12c-0.298,0.168-0.609,0.335-0.945,0.497c-0.043,0.021-0.088,0.041-0.132,0.061c-0.375,0.177-0.767,0.351-1.186,0.519c-0.012,0.005-0.024,0.009-0.036,0.014c-2.271,0.907-5.176,1.67-8.561,2.17c-0.017,0.002-0.034,0.004-0.051,0.007c-0.658,0.097-1.333,0.183-2.026,0.259c-0.113,0.012-0.232,0.02-0.346,0.032c-0.605,0.063-1.217,0.121-1.847,0.167c-0.288,0.021-0.59,0.031-0.883,0.049c-0.474,0.028-0.943,0.059-1.429,0.076C29.137,28.984,28.327,29,27.5,29s-1.637-0.016-2.432-0.044c-0.486-0.017-0.955-0.049-1.429-0.076c-0.293-0.017-0.595-0.028-0.883-0.049c-0.63-0.046-1.242-0.104-1.847-0.167c-0.114-0.012-0.233-0.02-0.346-0.032c-0.693-0.076-1.368-0.163-2.026-0.259c-0.017-0.002-0.034-0.004-0.051-0.007c-3.385-0.5-6.29-1.263-8.561-2.17c-0.012-0.004-0.024-0.009-0.036-0.014c-0.419-0.168-0.812-0.342-1.186-0.519c-0.043-0.021-0.089-0.041-0.132-0.061c-0.336-0.162-0.647-0.328-0.945-0.497c-0.07-0.04-0.144-0.079-0.212-0.12c-0.259-0.152-0.495-0.307-0.721-0.463c-0.086-0.06-0.175-0.119-0.257-0.18c-0.191-0.141-0.362-0.283-0.526-0.426c-0.089-0.078-0.179-0.156-0.26-0.236c-0.134-0.13-0.252-0.26-0.363-0.392c-0.078-0.092-0.153-0.184-0.219-0.277c-0.088-0.123-0.163-0.246-0.231-0.369c-0.054-0.099-0.102-0.198-0.143-0.297c-0.049-0.121-0.088-0.242-0.116-0.363C4.541,21.823,4.5,21.661,4.5,21.5c0-0.113,0.013-0.226,0.031-0.338c0.025-0.151,0.011-0.302-0.031-0.445v-7.424c0.12,0.109,0.257,0.216,0.387,0.324c0.072,0.06,0.139,0.12,0.215,0.18c0.3,0.236,0.624,0.469,0.975,0.696c0.073,0.047,0.155,0.093,0.231,0.14c0.294,0.183,0.605,0.362,0.932,0.538c0.121,0.065,0.242,0.129,0.367,0.193c0.365,0.186,0.748,0.367,1.151,0.542c0.066,0.029,0.126,0.059,0.193,0.087c0.469,0.199,0.967,0.389,1.485,0.573c0.143,0.051,0.293,0.099,0.44,0.149c0.412,0.139,0.838,0.272,1.279,0.401c0.159,0.046,0.315,0.094,0.478,0.138c0.585,0.162,1.189,0.316,1.823,0.458c0.087,0.02,0.181,0.036,0.269,0.055c0.559,0.122,1.139,0.235,1.735,0.341c0.202,0.036,0.407,0.07,0.613,0.104c0.567,0.093,1.151,0.178,1.75,0.256c0.154,0.02,0.301,0.043,0.457,0.062c0.744,0.09,1.514,0.167,2.305,0.233c0.195,0.016,0.398,0.028,0.596,0.042c0.633,0.046,1.28,0.084,1.942,0.114c0.241,0.011,0.481,0.022,0.727,0.031C25.712,18.979,26.59,19,27.5,19s1.788-0.021,2.65-0.05c0.245-0.009,0.485-0.02,0.727-0.031c0.662-0.03,1.309-0.068,1.942-0.114c0.198-0.015,0.4-0.026,0.596-0.042c0.791-0.065,1.561-0.143,2.305-0.233c0.156-0.019,0.303-0.042,0.457-0.062c0.599-0.078,1.182-0.163,1.75-0.256c0.206-0.034,0.411-0.068,0.613-0.104c0.596-0.106,1.176-0.219,1.735-0.341c0.088-0.019,0.182-0.036,0.269-0.055c0.634-0.142,1.238-0.297,1.823-0.458c0.163-0.045,0.319-0.092,0.478-0.138c0.441-0.129,0.867-0.262,1.279-0.401c0.147-0.05,0.297-0.098,0.44-0.149c0.518-0.184,1.017-0.374,1.485-0.573c0.067-0.028,0.127-0.058,0.193-0.087c0.403-0.176,0.786-0.356,1.151-0.542c0.125-0.064,0.247-0.128,0.367-0.193c0.327-0.175,0.638-0.354,0.932-0.538c0.076-0.047,0.158-0.093,0.231-0.14c0.351-0.227,0.675-0.459,0.975-0.696c0.075-0.06,0.142-0.12,0.215-0.18C50.243,13.509,50.38,13.402,50.5,13.293z M27.5,2c13.555,0,23,3.952,23,7.5s-9.445,7.5-23,7.5s-23-3.952-23-7.5S13.945,2,27.5,2z M50.5,45.703c-0.014,0.044-0.024,0.089-0.032,0.135C49.901,49.297,40.536,53,27.5,53S5.099,49.297,4.532,45.838c-0.008-0.045-0.019-0.089-0.032-0.131v-8.414c0.028,0.026,0.063,0.051,0.092,0.077c0.218,0.192,0.44,0.383,0.69,0.567C9.049,40.786,16.582,43,27.5,43c10.872,0,18.386-2.196,22.169-5.028c0.302-0.22,0.574-0.447,0.83-0.678l0.001-0.001V45.703z" />
                    </svg>
                    <?= $cml_db_request_amount; ?>
                    <div class="infoBox">
                        <div>DB Requests</div>
                        <div>
                            <?= $this->_generateDBTable($cml_db_request_query); ?>
                        </div>
                    </div>
                </div>
                <?php foreach ($this->customBarElements as $element) : ?>
                    <?= $element ?>
                <?php endforeach; ?>
            </div>

            <div class="cmlBarEnd">
                <div class="cmlBarVersion info-item">CML v<?= useTrait()::getFrameworkVersion() ?>
                    <div class="infoBox">
                        <?= $this->_generateConfigTable(cml_config()) ?>
                    </div>
                </div>
                <div class="cmlBarClose">x</div>
            </div>
        </div>
        <?php
        echo $this->minifyHTML(preg_replace('/\h+(?=<)/', ' ', ob_get_clean()));
    }

    /**
     * Sets the style for the information bar.
     * This method is private and is only called if the CML_DEBUG_BAR configuration is enabled.
     */
    private function _styleInfoBar()
    {
        if (cml_config('CML_DEBUG_BAR')) {
        ?>
            <style>
                .infoBox {
                    display: none;
                    position: fixed;
                    background-color: #1e1e1e;
                    color: white;
                    box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.5);
                    z-index: 9999;
                    border: 2px solid #4caf50;
                    padding: 5px;
                    max-height: 30vw;
                    overflow-y: auto;
                }

                .info-item {
                    background-color: #212121;
                }

                .info-item:hover {
                    cursor: pointer;
                    background-color: #121212;
                }

                .info-item:hover .infoBox {
                    display: block;
                    cursor: auto;
                }

                #cmlInfoBar {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    height: auto;
                    background-color: #161616;
                    border-top: 2px solid #4caf50;
                    overflow: hidden;
                    z-index: 9999;
                    box-shadow: 0px 2px 10px 0px black;
                }

                .cmlBarBegin,
                .cmlBarEnd {
                    display: flex;
                }

                #cmlInfoBar div:not(.cmlBarBegin, .cmlBarEnd, .infoBox) {
                    padding: 7px 15px;
                }

                .cmlNameRoute__type {
                    color: #6f6f6f;
                    margin-right: 5px;
                }

                .statusCode_200 {
                    background-color: #2c6b2c;
                }

                .statusCode_300 {
                    background-color: #b17a27;
                }

                .cmlBarClose {
                    background-color: #272727;
                    cursor: pointer;
                }

                .cmlDBRequest {
                    display: flex;
                    gap: 10px;
                }

                .cmlBarVersion {
                    font-size: 12px;
                    display: flex;
                    align-items: center;
                    padding: 0 10px;
                }

                .infoBox table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 13px;
                    color: #fff;
                }

                .infoBox th,
                .infoBox td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #444;
                }

                .infoBox th {
                    background-color: #333;
                    color: #fff;
                }

                .infoBox td {
                    background-color: #222;
                }

                .infoBox tr:nth-child(even) td {
                    background-color: #1a1a1a;
                }

                .infoBox tr:hover td {
                    background-color: #444;
                }

                .infoBox th:first-child,
                .infoBox td:first-child {
                    border-left: none;
                }

                .infoBox th:last-child,
                .infoBox td:last-child {
                    border-right: none;
                }

                .infoBox::-webkit-scrollbar {
                    width: 6px;
                }

                .infoBox::-webkit-scrollbar-thumb {
                    background-color: #888;
                    border-radius: 4px;
                }
            </style>
        <?php
        }
    }

    /**
     * Renders the script for the info bar functionality.
     *
     * This method is responsible for rendering the JavaScript code that controls the behavior of the info bar.
     * The info bar is displayed when the CML_DEBUG_BAR configuration option is set to true.
     *
     */
    private function _scriptInfoBar()
    {
        if (cml_config('CML_DEBUG_BAR')) {
        ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.info-item').forEach(function(element) {
                        element.addEventListener('mouseover', function() {
                            let infoBox = this.querySelector('.infoBox');
                            let barHeight = document.getElementById('cmlInfoBar').offsetHeight - 2;
                            let rect = this.getBoundingClientRect();
                            infoBox.style.left = rect.left + 'px';
                            infoBox.style.bottom = barHeight + 'px';
                            if (rect.right + infoBox.offsetWidth > window.innerWidth) {
                                infoBox.style.left = (window.innerWidth - infoBox.offsetWidth) + 'px';
                            } else {
                                infoBox.style.left = rect.left + 'px';
                            }
                        });
                    });

                    document.querySelector('.cmlBarClose').addEventListener('click', () => {
                        document.getElementById('cmlInfoBar').style.display = 'none';
                    });
                });
            </script>
        <?php
        }
    }

    /**
     * Generates a database table HTML markup based on the given data.
     *
     * @param array $data The data used to generate the table.
     * @return string The generated HTML table markup.
     */
    private function _generateDBTable(array $data)
    {
        $table = '<table>';
        $table .= '<thead><tr><th>Order</th><th>Time</th><th>Query</th><th>Params</th><th>Affected Rows</th><th>File</th></tr></thead>';
        $table .= '<tbody>';
        $index = 1;
        foreach ($data as $item) {
            $query = htmlspecialchars($item['query']);
            $params = htmlspecialchars(implode(", ", $item['params']));
            $table .= "<tr><td>$index</td><td>$item[executionTime]</td><td>$query</td><td>$params</td><td>" . (isset($item['affected_rows']) ? $item['affected_rows'] : '') . "</td><td>$item[file]:$item[line]</td></tr>";
            $index++;
        }
        $table .= '</tbody></table>';
        return $table;
    }

    private function _generateConfigTable(array $data)
    {
        $table = '<table>';
        $table .= '<thead><tr><th>Name</th><th>Value</th></tr></thead>';
        $table .= '<tbody>';
        foreach ($data as $name => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $table .= "<tr><td>$name</td><td>$value</td></tr>";
        }
        $table .= '</tbody></table>';
        return $table;
    }

    /**
     * Builds meta tags in the head section based on the provided array of meta attributes.
     */
    protected function _buildMetas()
    {
        foreach ($this->metas as $meta) : ?>
            <meta <?= $meta ?>>
        <?php endforeach;
    }

    /**
     * Builds content delivery network (CDN) links based on the provided array of CDNs.
     */
    protected function _buildCdns()
    {
        foreach ($this->cdns as $cdns) : ?>
            <?php foreach ($cdns as $tag => $attributes) : ?>
                <<?= $tag ?> <?= $attributes ?>>
                    <?php if ($tag == "script") : ?>
                </<?= $tag ?>>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach;
    }

    /**
     * Builds stylesheet links in the head section based on the provided array of styles.
     */
    protected function _buildStyles()
    {
        foreach ($this->styles as $style) : ?>
        <link rel="stylesheet" href=<?= $style ?>>
    <?php endforeach;
    }

    /**
     * Builds script tags in the head or body section based on the provided array of scripts.
     */
    protected function _buildScripts()
    {
        foreach ($this->scripts as $script) : ?>
        <script src=<?= $script ?>></script>
<?php endforeach;
    }
}
