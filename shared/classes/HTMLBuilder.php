<?php 
namespace Classes;
class HTMLBuilder {
    private $projectName = "";
    private $title = "";
    private $styles = [];
    private $scripts = [];
    private $metas = [];

    public function setProjectName($projectName) {
        $this->projectName = $projectName;
        $this->setTitle($this->projectName);
    }

    public function setTitle($title) {
        $this->title = $title;
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

    function assetUrl($path) {
        return '/' . ltrim($path, '/');
    }

    public function build() {
        $html = '<!DOCTYPE html>' . PHP_EOL;
        $html .= '<html>' . PHP_EOL;
        $html .= $this->init_comment($this->projectName, "CallMeLeon") . PHP_EOL;
        $html .= '<head>' . PHP_EOL;
        $html .= '<meta charset="UTF-8">' . PHP_EOL;
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
        foreach ($this->metas as $meta) {
            $html .= '<meta ' . $meta . '>' . PHP_EOL;
        }
        $html .= '<title>' . $this->title . '</title>' . PHP_EOL;
        
        foreach ($this->styles as $style) {
            $html .= '<link rel="stylesheet" type="text/css" href="' . $this->assetUrl($style) . '">' . PHP_EOL;
        }
        
        foreach ($this->scripts as $script) {
            $html .= '<script src="' . $this->assetUrl($script) . '"></script>' . PHP_EOL;
        }
        
        $html .= '</head>' . PHP_EOL;
        $html .= '<body>' . PHP_EOL;
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
        $num = rand(1,4);
        $dateFile = date ("d.m.Y H:i:s", getlastmod());
        $programmer = implode(', ', $programmer);

        $comment[] = "";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";
        $comment[] = "<!-- ".$this->center_comment("Willkommen im Quellcode von $titel")."-->";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";
        $comment[] = "<!-- ".$this->center_comment("Drittanbieter die verwendet werden sind:")."-->";
        $comment[] = "<!-- ".$this->center_comment("jQuery, Bootstrap, FontAwesome und Animate.css")."-->";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";

        switch ($num) {
            case 1:
                $comment[] = "<!-- ".$this->center_comment("   ______      ______  ___     __                    ")."-->";
                $comment[] = "<!-- ".$this->center_comment("  / ____/___ _/ / /  |/  /__  / /   ___  ____  ____  ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" / /   / __ `/ / / /|_/ / _ \/ /   / _ \/ __ \/ __ \ ")."-->";
                $comment[] = "<!-- ".$this->center_comment("/ /___/ /_/ / / / /  / /  __/ /___/  __/ /_/ / / / / ")."-->";
                $comment[] = "<!-- ".$this->center_comment("\____/\__,_/_/_/_/  /_/\___/_____/\___/\____/_/ /_/  ")."-->";
                break;
            case 2:
                $comment[] = "<!-- ".$this->center_comment("   ___          .    .   __   __        .                         ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" .'   \   ___   |    |   |    |    ___  /       ___    __.  , __  ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" |       /   `  |    |   |\  /|  .'   ` |     .'   ` .'   \ |'  `.")."-->";
                $comment[] = "<!-- ".$this->center_comment(" |      |    |  |    |   | \/ |  |----' |     |----' |    | |    |")."-->";
                $comment[] = "<!-- ".$this->center_comment("  `.__, `.__/| /\__ /\__ /    /  `.___, /---/ `.___,  `._.' /    |")."-->";
                break;
            case 3:
                $comment[] = "<!-- ".$this->center_comment(" __  __                 __      __          ")."-->";
                $comment[] = "<!-- ".$this->center_comment("|<< |  | |   |   |\ /| |   |   |    >>  | | ")."-->";
                $comment[] = "<!-- ".$this->center_comment("|   |><| |   |   | < | |<< |   |<< |  | |\| ")."-->";
                $comment[] = "<!-- ".$this->center_comment("|__ |  | |<< |<< |   | |__ |<< |__  <<  | |")."-->";
                break;
            case 4:
                $comment[] = "<!-- ".$this->center_comment("   ___|         |  |   \  |        |                         ")."-->";
                $comment[] = "<!-- ".$this->center_comment("  |       _` |  |  |  |\/ |   _ \  |       _ \   _ \   __ \  ")."-->";
                $comment[] = "<!-- ".$this->center_comment("  |      (   |  |  |  |   |   __/  |       __/  (   |  |   | ")."-->";
                $comment[] = "<!-- ".$this->center_comment(" \____| \__,_| _| _| _|  _| \___| _____| \___| \___/  _|  _| ")."-->";
                break;
        }

        $comment[] = "<!-- ".$this->center_comment(" ")."-->";
        $comment[] = "<!-- ".$this->center_comment("Diese Webseite wurde Programmiert von $programmer")."-->";
        $comment[] = "<!-- ".$this->center_comment("im Zeitraum von 2022-".date("Y"))."-->";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";
        $comment[] = "<!-- ".$this->center_comment("Letztes Update: $dateFile")."-->";
        $comment[] = "<!-- ".$this->center_comment(" ")."-->";

        foreach($comment as $endcomment){
            echo $endcomment;
        }
    }
}

// Beispielverwendung
// $htmlBuilder = new HTMLBuilder();
// $htmlBuilder->setTitle("Meine Webseite");
// $htmlBuilder->addStyle("styles.css");
// $htmlBuilder->addScript("script.js");

// echo $htmlBuilder->build();

?>