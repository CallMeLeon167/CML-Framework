<?php
namespace Classes\SessionTrait;

/**
 * A trait for managing sessions in PHP.
 */
trait SessionTrait {
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
?>
