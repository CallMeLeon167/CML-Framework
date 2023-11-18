<?php
namespace CML\Classes;

/**
 * A simple trait for managing events and event handlers.
 */
trait Events {

    /**
     * @var array $eventHandlers Associative array to store event handlers.
     */
    private $eventHandlers = [];

    /**
     * Register an event handler for a specific event.
     *
     * @param string $eventName The name of the event.
     * @param \Closure $callback The callback function to be executed when the event is triggered.
     */
    public function on(string $eventName, \Closure $callback) {
        // Register an event handler for a specific event
        $this->eventHandlers[$eventName][] = $callback;
    }

    /**
     * Trigger a specific event and call all registered event handlers.
     *
     * @param string $eventName The name of the event to trigger.
     * @param array $data (Optional) Any additional data to be passed to the event handlers.
     */
    public function trigger(string $eventName, array $data = []) {
        // Trigger a specific event and call all registered event handlers
        if(!isset($this->eventHandlers[$eventName])) {
            trigger_error("No handlers found for event '$eventName'", E_USER_ERROR);
            return;
        }
        foreach ($this->eventHandlers[$eventName] as $handler) {
            return call_user_func($handler, $data);
        }
    }

    /**
     * Check if a specific event has any registered handlers.
     *
     * @param string $eventName The name of the event.
     * @return bool True if event has handlers, false otherwise.
     */
    public function hasHandlers(string $eventName) {
        // Check if a specific event has any registered handlers
        return isset($this->eventHandlers[$eventName]);
    }
}
?>
