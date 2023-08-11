<?php 

namespace Classes;


class HTMLBuilder {
    private $title;
    private $styles = [];
    private $scripts = [];

    public function setTitle($title) {
        $this->title = $title;
    }

    public function addStyle($href) {
        $this->styles[] = $href;
    }

    public function addScript($src) {
        $this->scripts[] = $src;
    }

    public function build() {
        $html = '<!DOCTYPE html>' . PHP_EOL;
        $html .= '<html>' . PHP_EOL;
        $html .= '<head>' . PHP_EOL;
        $html .= '<title>' . $this->title . '</title>' . PHP_EOL;
        
        foreach ($this->styles as $style) {
            $html .= '<link rel="stylesheet" type="text/css" href="' . $style . '">' . PHP_EOL;
        }
        
        foreach ($this->scripts as $script) {
            $html .= '<script src="' . $script . '"></script>' . PHP_EOL;
        }
        
        $html .= '</head>' . PHP_EOL;
        $html .= '<body>' . PHP_EOL;
        // Hier könntest du weiteren Inhalt hinzufügen
        
        return $html;
    }
}

// Beispielverwendung
// $htmlBuilder = new HTMLBuilder();
// $htmlBuilder->setTitle("Meine Webseite");
// $htmlBuilder->addStyle("styles.css");
// $htmlBuilder->addScript("script.js");

// echo $htmlBuilder->build();

?>