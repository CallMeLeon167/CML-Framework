<?php 
namespace CML\Classes;

/**
 * The HTMLBuilder class is used to build HTML documents with customizable elements.
 */
class HTMLBuilder {
    use Functions\Functions;
    
    private string $projectName = "";
    private string $title = "";
    private string $favicon = "";
    private string $header = "";
    private string $footer = "";
    private string $bodyAttr = "";
    private string $htmlAttr = "";
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
     */
    public function addHeader(string $header = '') {
        $this->addContent('HEADER_FILE', $header, $this->header);
    }

    /**
     * Adds a footer element to the HTML document.
     *
     * @param string $footer The footer element to add.
     */
    public function addFooter(string $footer = '') {
        $this->addContent('FOOTER_FILE', $footer, $this->footer);
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
     * Generic function to add content (header or footer) to the HTML document.
     *
     * @param string $envKey The environment variable key for the content file.
     * @param string $content The content to add.
     * @param string &$property The property to store the content in.
     */
    protected function addContent(string $envKey, string $content, string &$property) {
        $contentFile = $_ENV[$envKey] ?? '';

        if (empty($contentFile) && empty($content)) {
            trigger_error("Could not set the $envKey", E_USER_ERROR);
            return;
        }

        if (!empty($content)) {
            $property = $content;
        } else {
            if (file_exists(self::getRootPath($contentFile))) {
                ob_start();
                include self::getRootPath($contentFile);
                $property = ob_get_clean();
            } else {
                trigger_error("$envKey file does not exist: $contentFile", E_USER_ERROR);
            }
        }
    }
    
    /**
     * Adds a resource link to the HTML document.
     *
     * @param string $path The path to the resource.
     * @param array &$container The container (styles or scripts) to which the resource should be added.
     */
    private function addResource(string $path, array &$container) {
        $envKey = $container === $this->styles ? 'STYLE_PATH' : 'SCRIPT_PATH';
        $envPath = $_ENV[$envKey] ?? '';
        $fullPath = $envPath ? $envPath . $path : $path;

        if (!file_exists(self::getRootPath($fullPath))) {
            $resourceType = $container === $this->styles ? 'stylesheet' : 'script';
            return trigger_error("Could not find $resourceType file => '" . htmlentities($fullPath) . "'", E_USER_ERROR);
        }

        if (filesize($fullPath) !== 0){
            $container[] = self::assetUrl($fullPath);
        } 
    }

    /**
     * Adds a stylesheet link to the HTML document.
     *
     * @param string $href The path to the stylesheet.
     */
    public function addStyle(string $href) {
        if($href) $this->addResource($href, $this->styles);
    }

    /**
     * Adds a script link to the HTML document.
     *
     * @param string $src The path to the script.
     */
    public function addScript(string $src) {
        if($src) $this->addResource($src, $this->scripts);
    }

    /**
     * Compresses CSS or JavaScript by removing whitespace and comments.
     *
     * @param string $path The path to the CSS or JavaScript file to compress.
     * @param string $envPath The environment path for the file.
     * @param string $fileExtension The file extension to use for the compressed file.
     * @return string The path to the compressed file.
     */
    public static function compressFile(string $path, string $envPath, string $fileExtension):string {
        $newFileName = str_replace($fileExtension, ".min{$fileExtension}", $path);

        $content = file_get_contents($envPath ? $envPath . $path : $path);
        if (empty($content)) return false;
        $content = preg_replace('/\/\/[^\n\r]*|\/\*[\s\S]*?\*\//', '', $content);
        $content = preg_replace('/\s*([{}:;,=()])\s*/', '$1', $content);
        $content = preg_replace('/;\s*}/', '}', $content);
        $content = preg_replace('/\s+/', ' ', $content);

        $file = fopen($envPath . $newFileName, "w");
        fwrite($file, $content);
        fclose($file);

        return $newFileName;
    }

    /**
     * Compresses CSS or JavaScript by removing whitespace and comments.
     *
     * @param string $path The path to the CSS or JavaScript file to compress.
     * @param string $envPath The environment path for the file.
     * @return string The path to the compressed file.
     */
    public static function compress(string $path):string {
        $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
        
        if ($fileExtension === 'css') {
            return self::compressFile($path, $_ENV['STYLE_PATH'] ?? '', '.css');
        } elseif ($fileExtension === 'js') {
            return self::compressFile($path, $_ENV['SCRIPT_PATH'] ?? '', '.js');
        } else {
            return $path;
        }
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
     * Register a hook to place content at a specific location in the HTML document.
     *
     * @param string   $hookName      The name of the hook (e.g., 'before_head', 'after_head', 'top_body', etc.).
     * @param mixed    $contentSource The file path, a callable function, or HTML code to provide content.
     */
    public function addHook(string $hookName, $contentSource) {
        if(!in_array($hookName, $this->regHooks)){
            trigger_error("Invalid hookname: $hookName", E_USER_ERROR);
        }
        $this->hooks[$hookName] = [
            'source' => $contentSource,
        ];
    }

    /**
     * Get the content for a specific hook and return it.
     *
     * @param string $hookName The name of the hook (e.g., 'before_head', 'after_head', 'top_body', etc.).
     *
     * @return string The content for the specified hook.
     */
    protected function getHookContent(string $hookName) {
        if (isset($this->hooks[$hookName])) {
            $hook = $this->hooks[$hookName];
            $contentSource = $hook['source'];

            if (is_callable($contentSource)) {
                $content = call_user_func($contentSource);
                return is_string($content) ? $content : '';
            } elseif (file_exists(self::getRootPath($contentSource))) {
                ob_start();
                include self::getRootPath($contentSource);
                return ob_get_clean();
            } elseif (is_string($contentSource)) {
                return $contentSource;
            } else {
                trigger_error("Invalid content source for the hook: $hookName", E_USER_ERROR);
            }
        }
        return '';
    }

    /**
     * Builds the HTML document with configured elements and displays it.
     */
    public function build() {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html <?= $this->htmlAttr?>>
        <?= $this->getHookContent('before_head'); ?>
        <head>
            <?= $this->getHookContent('top_head'); ?>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?php foreach ($this->metas as $meta): ?>
                <meta <?= $meta ?>>
            <?php endforeach; ?>
            <title><?= $this->title ?></title>
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
                <link rel="stylesheet" type="text/css" href="<?= $style ?>">
            <?php endforeach; ?>

            <?php foreach ($this->scripts as $script): ?>
                <script src="<?= $script ?>" type="text/javascript"></script>
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

        echo ob_get_clean();
        echo $this->getHookContent('bottom_body');
    }

    /**
     * Close the application correctly.
     */
    public function build_end() {
        echo $this->footer;
        echo PHP_EOL .'</body>';
        echo $this->getHookContent('after_body');
        echo PHP_EOL .'</html>';
        exit;
    }
}
?>