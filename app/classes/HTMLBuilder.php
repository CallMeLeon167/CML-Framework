<?php 
namespace CML\Classes;

/**
 * The HTMLBuilder class is used to build HTML documents with customizable elements.
 */
class HTMLBuilder {
    use Traits\Functions;
    
    private bool $showComments = true;
    private string $projectName = "";
    private string $title = "";
    private string $favicon = "";
    private string $author = "";
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
     * Sets the author(s) of the HTML document.
     *
     * @param string ...$author The author(s) of the HTML document.
     */
    public function setAuthor(string ...$author) {
        $this->author = implode(", ", $author);
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
            if (file_exists($contentFile)) {
                ob_start();
                include $contentFile;
                $property = ob_get_clean();
            } else {
                trigger_error("$envKey file does not exist: $contentFile", E_USER_ERROR);
            }
        }
    }
    

    /**
     * Adds a stylesheet link to the HTML document.
     *
     * @param string $href The path to the stylesheet.
     */
    public function addStyle(string $href) {
        $SP = $_ENV['STYLE_PATH'] ?? '';
        $path = $SP ? $SP.$href : $href;

        if (!file_exists(dirname(__DIR__) . '/../' . $path)) {
            trigger_error("Could not find stylesheet file => '" . htmlentities($path) . "'", E_USER_ERROR);
        }
    
        $this->styles[] = self::assetUrl($path);
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
     * Adds a script link to the HTML document.
     *
     * @param string $src The path to the script.
     */
    public function addScript(string $src) {
        $SP = $_ENV['SCRIPT_PATH'] ?? '';
        $path = $SP ? $SP.$src : $src;
    
        if (!file_exists(dirname(__DIR__) . '/../' . $path)) {
            trigger_error("Could not find script file => '" . htmlentities($path) . "'", E_USER_ERROR);
        }

        $this->scripts[] = self::assetUrl($path);
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
     * Disables HTML comments in the document.
     */
    public function disableComments() {
        $this->showComments = false;
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
            } elseif (file_exists(dirname(__DIR__) . '/../' . $contentSource)) {
                ob_start();
                include dirname(__DIR__) . '/../' . $contentSource;
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
        <?php if ($this->showComments == true) $this->init_comment($this->projectName, $this->author); ?>
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
    

    /**
     * Centers a comment in the HTML document.
     *
     * @param string $comment The comment text to be centered.
     * @param bool $center Indicates if the comment should be centered (default is true).
     * @return string The centered comment.
     */
    public static function center_comment(string $comment, bool $center = true): string {
        $stringLen = 80;
    
        if (!$center) {
            $comment = ' ' . $comment;
        }
    
        $spaces = max(0, $stringLen - strlen($comment));
    
        $leftSpaces = $center ? (int)($spaces / 2) : 0;
        $rightSpaces = $spaces - $leftSpaces;
    
        $centeredComment = str_repeat(' ', $leftSpaces) . $comment . str_repeat(' ', $rightSpaces);
    
        return $centeredComment;
    }
    

    /**
     * Initializes and displays a centered comment in the HTML document with a randomly chosen ASCII art style.
     *
     * @param string $title The application name.
     * @param string ...$programmer The programmer's names.
     */
    public function init_comment(string $title, string ...$programmer) {
        $comments = [];

        $comments[] = $this->comment(" ");
        $comments[] = $this->comment("Welcome to the source code of $title");
        $comments[] = $this->comment(" ");

        $artStyles = $this->getComment();
        $randomStyle = $artStyles[array_rand($artStyles)];

        $dateModified = date("d.m.Y H:i:s", getlastmod());
        $programmerNames = implode(', ', $programmer);

        $comments[] = $randomStyle;

        $comments[] = $this->comment(" ");
        if(!empty($programmerNames)){
            $comments[] = $this->comment("This website was developed by $programmerNames");
        } else {
            $comments[] = $this->comment("This website was developed");
        }
        $comments[] = $this->comment("in the year " . date("Y"));
        $comments[] = $this->comment(" ");
        $comments[] = $this->comment("Last update: $dateModified");
        $comments[] = $this->comment(" ");

        foreach ($comments as $comment) {
            echo $comment . PHP_EOL;
        }
    }

    /**
     * Returns an array of ASCII art styles.
     *
     * @return array The array of ASCII art styles.
     */
    private function getComment(): array{
        $artStyles = [
            $this->comment("  _____             _      _       _         ").
            $this->comment(" |  __ \           | |    (_)     | |        ").
            $this->comment(" | |  | | _____   _| |     _ _ __ | | _____  ").
            $this->comment(" | |  | |/ _ \ \ / / |    | | '_ \| |/ / __| ").
            $this->comment(" | |__| |  __/\ V /| |____| | | | |   <\__ \ ").
            $this->comment(" |_____/ \___| \_/ |______|_|_| |_|_|\_\___/ "),

            $this->comment("    ___            __ _       _         ").
            $this->comment("   /   \_____   __/ /(_)_ __ | | _____  ").
            $this->comment("  / /\ / _ \ \ / / / | | '_ \| |/ / __| ").
            $this->comment(" / /_//  __/\ V / /__| | | | |   <\__ \ ").
            $this->comment("/___,' \___| \_/\____/_|_| |_|_|\_\___/ "),
            
        ];

        return $artStyles;
    }
    
    /**
     * Generates an HTML comment with the provided content.
     *
     * @param string $comment The content of the comment.
     * @return string The HTML comment.
     */
    private function comment(string $comment): string {
        return "<!-- ".$this->center_comment($comment)." -->". PHP_EOL;
    }
}
?>