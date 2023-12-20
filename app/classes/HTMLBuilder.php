<?php 
namespace CML\Classes;

/**
 * Class HTMLBuilder
 *
 * @author CallMeLeon <kontakt@callmeleon.de>
 * @see https://docs.callmeleon.de/cml#html-builder
 */
abstract class HTMLBuilder {
    use Functions\Functions;
    
    private bool $minifyHTML = false;
    private string $ajaxUrl = "";
    private string $projectName = "";
    private string $title = "";
    private string $favicon = "";
    private string $header = "";
    private string $footer = "";
    private string $bodyAttr = "";
    private string $htmlAttr = "";
    private string $lang = "en";
    private string $charset = "UTF-8";
    private array $styles = [];
    private array $scripts = [];
    private array $metas = [];
    private array $cdns = [];
    private array $hooks = [];
    private array $regHooks = [
        'before_head',
        'top_head',
        'bottom_head',
        'after_head',
        'before_body',
        'top_body',
        'bottom_body',
        'after_body',
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
     * @param string $header The header element to add.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    public function addHeader(string $header = '', array $variables = []) {
        $this->addContent(HEADER_FILE, $header, $this->header, $variables);
    }

    /**
     * Adds a footer element to the HTML document.
     *
     * @param string $footer The footer element to add.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    public function addFooter(string $footer = '', array $variables = []) {
        $this->addContent(FOOTER_FILE, $footer, $this->footer, $variables);
    }

    /**
     * Set HTML tag attributes for the document.
     *
     * @param string $attr The HTML tag attributes to be added.
     */
    public function addHtmlTagAttributes(string $attr = '') {
        $this->htmlAttr = $attr;
    }

    /**
     * Set body tag attributes for the document.
     *
     * @param string $attr The body tag attributes to be added.
     */
    public function addBodyTagAttributes(string $attr = '') {
        $this->bodyAttr = $attr;
    }

    /**
     * Set the lang attribute for the document.
     * 
     * @param string $lang The lang attribute of the document.
     */
    public function setLang(string $lang) {
        $this->lang = $lang;
    }

    /**
     * Get the lang attribute of the document.
     * 
     * @return string The lang attribute of the document.
     */
    public function getLang():string {
        return $this->lang;
    }

    /**
     * Set the charset for the document.
     * 
     * @param string $charset The charset attribute of the document.
     */
    public function setCharset(string $charset) {
        $this->charset = $charset;
    }

    /**
     * Add a CDN link to the stored resources.
     *
     * @param string $type The type of the CDN link (e.g., 'link', 'script', etc.).
     * @param string $attr The attribute information for the CDN link.
     */
    public function addCDN(string $type, string $attr) {
        $type = strtolower($type);
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
     */
    public function setAjaxUrl(){
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $servername = $_SERVER['SERVER_NAME'];
        $currentPath = dirname($_SERVER['PHP_SELF']);
        $fullUrl = $protocol . '://' . $servername . ($currentPath !== '/' ? $currentPath : '');
        $ajaxUrl = $fullUrl."/app/admin/cml-ajax.php";
        $this->ajaxUrl = $ajaxUrl;
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
            trigger_error("Invalid hook name: $hookName", E_USER_ERROR);
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
        if ($href) $this->addResource($href, $this->styles, $attributes, $fromRoot);
    }

    /**
     * Adds a script link to the HTML document.
     *
     * @param string $src The path to the script.
     * @param string|array $attributes Additional attributes for the script element (optional).
     * @param bool $fromRoot Whether the path is relative to the document root.
     */
    public function addScript(string $src, $attributes = "", bool $fromRoot = false) {
        if ($src) $this->addResource($src, $this->scripts, $attributes, $fromRoot);
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
            include $path;
            return $this->minifyHTML(ob_get_clean());
        } else {
            trigger_error(htmlentities("Component $component | not found in ".$path), E_USER_ERROR);
        }
    }

    /**
     * Generic function to add content (header or footer) to the HTML document.
     *
     * @param string $const
     * @param string $content The content to add.
     * @param string &$property The property to store the content in.
     * @param array $variables Associative array of variables to be extracted and made available in the included file.
     */
    protected function addContent(string $const, string $content, string &$property, array $variables = []) {
        $contentFile = $const ?? '';
        if (empty($contentFile) && empty($content)) {
            return trigger_error("Could not set the $const", E_USER_ERROR);
        }

        if (!empty($content)) {
            $property = $content;
        } else {
            if (file_exists(self::getRootPath($contentFile))) {
                extract($variables);
                ob_start();
                include self::getRootPath($contentFile);
                $property = ob_get_clean();
            } else {
                trigger_error("$const file does not exist: $contentFile", E_USER_ERROR);
            }
        }
    }

    /**
     * Converts an associative array to HTML attribute string.
     *
     * @param array $attributes
     * @return string
     */
    protected function arrayToAttributes(array $attributes): string {
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
    protected function addResource(string $path, array &$container, $attributes = "", bool $fromRoot = false) {
        $const = $container === $this->styles ? 'STYLE_PATH' : 'SCRIPT_PATH';
        
        $fullPath = $fromRoot ? "/$path" : (constant($const) ?? '') . $path;

        if (!file_exists(self::getRootPath($fullPath))) {
            $resourceType = $container === $this->styles ? 'stylesheet' : 'script';
            return trigger_error("Could not find $resourceType file => '" . htmlentities($fullPath) . "'", E_USER_ERROR);
        }

        if (!is_array($attributes)) {
            $attributes = !empty($attributes) ? " $attributes" : "";
        } else {
            $attributes = $this->arrayToAttributes($attributes);
        }

        if (filesize(self::getRootPath($fullPath)) !== 0) {
            $container[] = '"' . self::assetUrl($fullPath) . '"' . $attributes;
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
    protected static function compressFile(string $path, string $configPath, string $fileExtension):string {
        $newFileName = str_replace($fileExtension, ".min{$fileExtension}", $path);

        $content = file_get_contents(self::getRootPath($configPath ? $configPath . $path : $path));
        if (empty($content)) return false;
        $content = preg_replace('/\/\/[^\n\r]*|\/\*[\s\S]*?\*\//', '', $content);
        $content = preg_replace('/\s*([{}:;,=()])\s*/', '$1', $content);
        $content = preg_replace('/;\s*}/', '}', $content);
        $content = preg_replace('/\s+/', ' ', $content);

        $file = fopen(self::getRootPath($configPath) . $newFileName, "w");
        fwrite($file, $content);
        fclose($file);

        return $newFileName;
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
            return self::compressFile($path, STYLE_PATH ?? '', '.css');
        } elseif ($fileExtension === 'js') {
            return self::compressFile($path, SCRIPT_PATH ?? '', '.js');
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
    protected function getHookContent(string $hookName) {
        if (isset($this->hooks[$hookName])) {
            $hooks = $this->hooks[$hookName];
            $this->sortByKey($hooks, "level");

            foreach ($hooks as $hook) {
                $contentSource = $hook['source'];
                
                if (is_callable($contentSource)) {
                    $content = call_user_func($contentSource);
                    echo is_string($content) ? $content : '';
                } elseif (file_exists(self::getRootPath($contentSource))) {
                    ob_start();
                    include self::getRootPath($contentSource);
                    echo ob_get_clean();
                } elseif (is_string($contentSource)) {
                    echo $contentSource;
                } else {
                    trigger_error("Invalid content source for the hook: $hookName", E_USER_ERROR);
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
    protected function sortByKey(array &$array, $key) {
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
     * Builds the HTML document with configured elements and displays it.
     */
    public function build() {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="<?= $this->lang ?>"<?= $this->htmlAttr?>>
        <?= $this->getHookContent('before_head'); ?>
        <head>
            <?= $this->getHookContent('top_head'); ?>
            <meta charset="<?= $this->charset ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?php foreach ($this->metas as $meta): ?>
                <meta <?= $meta ?>>
            <?php endforeach; ?>
            <title><?= empty($this->title) ? APP_NAME : $this->title?></title>
            <?= !empty($this->ajaxUrl) ? "<script>let ajax_url = '{$this->ajaxUrl}'</script>" : ''?>
            <link rel="icon" type="image/x-icon" href="<?= self::assetUrl($this->favicon) ?>">
            <?php foreach ($this->cdns as $cdns): ?>
                <?php foreach ($cdns as $tag => $attributes): ?>
                    <<?= $tag ?> <?= $attributes ?>>
                    <?php if ($tag == "script"): ?>
                        </<?= $tag ?>>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <?php foreach ($this->styles as $style): ?>
                <link rel="stylesheet" href=<?= $style ?>>
            <?php endforeach; ?>
            <?php foreach ($this->scripts as $script): ?>
                <script src=<?= $script ?>></script>
            <?php endforeach; ?>
            <?= $this->getHookContent('bottom_head'); ?>
        </head>
        <?= $this->getHookContent('after_head'); ?>

        <?= $this->getHookContent('before_body'); ?>
        <body <?= $this->bodyAttr ?>>
        <?= $this->getHookContent('top_body'); ?>
        <?php
        echo $this->header;
    
        // Additional content can be added here

        echo $this->minifyHTML(ob_get_clean());
    }

    /**
     * Close the application correctly.
     */
    public function build_end() {
        ob_start();
        echo $this->minifyHTML($this->getHookContent('bottom_body'));
        echo $this->footer;
        echo '</body>';
        echo $this->getHookContent('after_body');
        echo '</html>';
        echo $this->minifyHTML(ob_get_clean());
        exit;
    }
}
?>