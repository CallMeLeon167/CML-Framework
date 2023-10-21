<?php 
namespace Classes;

/**
 * The HTMLBuilder class is used to build HTML documents with customizable elements.
 */
class HTMLBuilder {
    use Traits\Traits;
    
    private string $projectName = "";
    private string $title = "";
    private string $favicon = "";
    private string $author = "";
    private string $baseUrl = "";
    private string $header = "";
    private array $styles = [];
    private array $scripts = [];
    private array $metas = [];
    private array $cdns = [];
    private bool $showComments = true;

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
    public function addHeader(string $header) {
        $this->header = $header;
    }

    /**
     * Adds a stylesheet link to the HTML document.
     *
     * @param string $href The path to the stylesheet.
     */
    public function addStyle(string $href) {
        $SP = $_ENV['STYLE_PATH'] ?? '';
        $path = $SP ? $SP.$href : $href;

        if (!file_exists($path)) {
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
    
        if (!file_exists($path)) {
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
     * Builds the HTML document with configured elements and displays it.
     */
    public function build() {
        $html = '<!DOCTYPE html>' . PHP_EOL;
        $html .= '<html>' . PHP_EOL;
        if ($this->showComments == true) $html .= $this->init_comment($this->projectName, $this->author) . PHP_EOL;
        $html .= '<head>' . PHP_EOL;
        $html .= '<meta charset="UTF-8">' . PHP_EOL;
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
        foreach ($this->metas as $meta) {
            $html .= '<meta ' . $meta . '>' . PHP_EOL;
        }
        $html .= '<title>' . $this->title . '</title>' . PHP_EOL;
        $html .= '<link rel="icon" type="image/x-icon" href="' . self::assetUrl($this->favicon) . '">' . PHP_EOL;

        foreach ($this->cdns as $cdns) {
            foreach ($cdns as $tag => $attributes) {
                $html .= "<$tag $attributes>";
                if ($tag == "script") {
                    $html .= "</$tag>";
                }
                $html .= PHP_EOL;
            }
        }

        foreach ($this->styles as $style) {
            $html .= '<link rel="stylesheet" type="text/css" href="' . $style . '">' . PHP_EOL;
        }

        foreach ($this->scripts as $script) {
            $html .= '<script src="' . $script . '" type="text/javascript"></script>' . PHP_EOL;
        }
        
        $html .= '</head>' . PHP_EOL;
        $html .= '<body>' . PHP_EOL;

        $html .= $this->header . PHP_EOL;

        // Additional content can be added here
        
        echo $html;
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