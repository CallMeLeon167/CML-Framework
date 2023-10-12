<?php
namespace Classes\Traits;

trait Traits{
    /**
     * Generate an absolute URL for an asset based on the provided path.
     *
     * This function appends the provided asset path to the base URL of the script,
     * ensuring that the URL is properly formatted, including the appropriate leading slash.
     *
     * @param string $path The path to the asset, relative to the root of the application.
     * @return string The absolute URL of the asset.
     */
    public function assetUrl($path) {
        $this->baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        return ($this->baseUrl == "/") ? "/" . ltrim($path, '/') : $this->baseUrl . "/" . ltrim($path, '/');
    }
}