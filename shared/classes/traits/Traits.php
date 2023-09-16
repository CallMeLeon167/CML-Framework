<?php
namespace Classes\Traits;

trait Traits{
    public function assetUrl($path) {
        $this->baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        return ($this->baseUrl == "/") ? "/" . ltrim($path, '/') : $this->baseUrl . "/" . ltrim($path, '/');
    }
}