<?php
namespace Classes\Traits;

trait Functions{
    /**
     * Generate an absolute URL for an asset based on the provided path.
     *
     * This function appends the provided asset path to the base URL of the script,
     * ensuring that the URL is properly formatted, including the appropriate leading slash.
     *
     * @param string $path The path to the asset, relative to the root of the application.
     * @return string The absolute URL of the asset.
     */
    public function assetUrl(string $path): string {
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        return ($baseUrl == "/") ? "/" . ltrim($path, '/') : $baseUrl . "/" . ltrim($path, '/');
    }
}

/**
 * A trait for managing sessions in PHP.
 */
trait Session {
    /**
     * Starts or resumes a session if not already started.
     */
    public function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Sets a value in the session.
     *
     * @param string $key The key under which the data will be stored.
     * @param mixed $value The data to be stored in the session.
     */
    public function setSessionData(string $key, $value) {
        $this->startSession();
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieves a value from the session.
     *
     * @param string $key The key to retrieve data from.
     *
     * @return mixed|null The data stored under the specified key, or null if not found.
     */
    public function getSessionData(string $key) {
        $this->startSession();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    }

    /**
     * Unsets (removes) a value from the session.
     *
     * @param string $key The key of the data to remove.
     */
    public function unsetSessionData(string $key) {
        $this->startSession();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Ends the session and destroys all session data.
     */
    public function endSession() {
        $this->startSession();
        session_destroy();
    }
}