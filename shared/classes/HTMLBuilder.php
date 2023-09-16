<?php 
namespace Classes;
class HTMLBuilder{
    use Traits\Traits;
    private string $projectName = "";
    private string $title = "";
    private string $favicon = "";
    private string $author = "CallMeLeon";
    private string $baseUrl = "";
    private array $header = [];
    private array $styles = [];
    private array $scripts = [];
    private array $metas = [];
    private bool $showComments = true;

    public function setProjectName($projectName) {
        $this->projectName = $projectName;
        $this->setTitle($this->projectName);
    }

    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function setAuthor(...$author) {
        $this->author = implode(", ", $author);
    }

    public function setFavicon($favicon) {
        $this->favicon = $favicon;
    }

    public function addHeader($header) {
        $this->header[] = $header;
    }

    public function addStyle($href) {
        $this->styles[] = $href;
    }

    public function addScript($src) {
        $this->scripts[] = $src;
    }

    public function addMeta($attrs) {
        $this->metas[] = $attrs;
    }

    public function disableComments() {
        $this->showComments = false;
    }

    public function build() {
        $html = '<!DOCTYPE html>' . PHP_EOL;
        $html .= '<html>' . PHP_EOL;
        if($this->showComments == true) $html .= $this->init_comment($this->projectName, $this->author) . PHP_EOL;
        $html .= '<head>' . PHP_EOL;
        $html .= '<meta charset="UTF-8">' . PHP_EOL;
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
        foreach ($this->metas as $meta) {
            $html .= '<meta ' . $meta . '>' . PHP_EOL;
        }
        $html .= '<title>' . $this->title . '</title>' . PHP_EOL;
        $html .= '<link rel="icon" type="image/x-icon" href="'.$this->assetUrl($this->favicon).'">' . PHP_EOL;
        
        foreach ($this->styles as $style) {
            $html .= '<link rel="stylesheet" type="text/css" href="' . $this->assetUrl($style) . '">' . PHP_EOL;
        }
        
        foreach ($this->scripts as $script) {
            $html .= '<script src="' . $this->assetUrl($script) . '"></script>' . PHP_EOL;
        }
        
        $html .= '</head>' . PHP_EOL;
        $html .= '<body>' . PHP_EOL;
        
        foreach ($this->header as $header) {
            $html .= $header . PHP_EOL;
        }

        // Hier könntest du weiteren Inhalt hinzufügen
        
        echo $html;
    }

    /**
     * @param string $comment String der Zentriert werden soll.
     * @return string Returned einen zentrierten string.
     */
    public static function center_comment(string $comment, bool $center = true):string{
        $centeredCommend = "";
        $commentLen = strlen($comment);
        $stringLen = 84;
        $spaces = $stringLen - $commentLen;
        $cleanSpaces = intval($spaces / 2.2);

        if(!$center){
            $centeredCommend .= " ".$comment;
            $spaces = $spaces - 1;
        }
        for ($i=0; $i < $spaces; $i++) { 
            if ($center && $i == $cleanSpaces) {
                $centeredCommend.= " ". $comment;
            } else {
                $centeredCommend .= " ";
            }
        }
        return $centeredCommend;
    }

    /**
     * @param string $titel Anwendungsname.
     * @param string $programmer Namen der Programmierer.
     */
    public function init_comment(string $titel, string ...$programmer){
        $comment = array();
        $num = rand(1,7);
        $dateFile = date ("d.m.Y H:i:s", getlastmod());
        $programmer = implode(', ', $programmer);

        $comment[] = "";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";
        $comment[] = "<!-- ".$this->center_comment("Willkommen im Quellcode von $titel")."-->";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";

        switch ($num) {
            case 1:
                $comment[] = "<!-- ".$this->center_comment("  _____             _      _       _         ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" |  __ \           | |    (_)     | |        ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" | |  | | _____   _| |     _ _ __ | | _____  ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" | |  | |/ _ \ \ / / |    | | '_ \| |/ / __| ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" | |__| |  __/\ V /| |____| | | | |   <\__ \ ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" |_____/ \___| \_/ |______|_|_| |_|_|\_\___/ ")."-->";
                break;
            case 2:
                $comment[] = "<!-- ".$this->center_comment("    ___            __ _       _         ")."-->";
                $comment[] = "<!-- ".$this->center_comment("   /   \_____   __/ /(_)_ __ | | _____  ")."-->";
                $comment[] = "<!-- ".$this->center_comment("  / /\ / _ \ \ / / / | | '_ \| |/ / __| ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" / /_//  __/\ V / /__| | | | |   <\__ \ ")."-->";
                $comment[] = "<!-- ".$this->center_comment("/___,' \___| \_/\____/_|_| |_|_|\_\___/ ")."-->";
                break;
            case 3:
                $comment[] = "<!-- ".$this->center_comment(" ____          __    _     _       ")."-->";
                $comment[] = "<!-- ".$this->center_comment("|    \ ___ _ _|  |  |_|___| |_ ___ ")."-->";
                $comment[] = "<!-- ".$this->center_comment("|  |  | -_| | |  |__| |   | '_|_ -|")."-->";
                $comment[] = "<!-- ".$this->center_comment("|____/|___|\_/|_____|_|_|_|_,_|___|")."-->";
                break;
            case 4:
                $comment[] = "<!-- ".$this->center_comment("    ____            __    _       __       ")."-->";
                $comment[] = "<!-- ".$this->center_comment("   / __ \___ _   __/ /   (_)___  / /_______")."-->";
                $comment[] = "<!-- ".$this->center_comment("  / / / / _ \ | / / /   / / __ \/ //_/ ___/")."-->";
                $comment[] = "<!-- ".$this->center_comment(" / /_/ /  __/ |/ / /___/ / / / / ,< (__  ) ")."-->";
                $comment[] = "<!-- ".$this->center_comment("/_____/\___/|___/_____/_/_/ /_/_/|_/____/  ")."-->";
                break;
            case 5:
                $comment[] = "<!-- ".$this->center_comment(" ___            _    _       _       ")."-->";
                $comment[] = "<!-- ".$this->center_comment("| . \ ___  _ _ | |  <_>._ _ | |__ ___")."-->";
                $comment[] = "<!-- ".$this->center_comment("| | |/ ._>| | || |_ | || ' || / /<_-<")."-->";
                $comment[] = "<!-- ".$this->center_comment("|___/\___.|__/ |___||_||_|_||_\_\/__/")."-->";
                break;
            case 6:
                $comment[] = "<!-- ".$this->center_comment(" ____ ____ ____ ____ ____ ____ ____ ____ ")."-->";
                $comment[] = "<!-- ".$this->center_comment("||D |||e |||v |||L |||i |||n |||k |||s ||")."-->";
                $comment[] = "<!-- ".$this->center_comment("||__|||__|||__|||__|||__|||__|||__|||__||")."-->";
                $comment[] = "<!-- ".$this->center_comment("|/__\|/__\|/__\|/__\|/__\|/__\|/__\|/__\|")."-->";
                break;
        }

        $comment[] = "<!-- ".$this->center_comment(" ")."-->";
        $comment[] = "<!-- ".$this->center_comment("Diese Webseite wurde Programmiert von $programmer")."-->";
        $comment[] = "<!-- ".$this->center_comment("im Zeitraum von ".date("Y"))."-->";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";
        $comment[] = "<!-- ".$this->center_comment("Letztes Update: $dateFile")."-->";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";

        foreach($comment as $endcomment){
            echo $endcomment;
        }
    }
}
?>