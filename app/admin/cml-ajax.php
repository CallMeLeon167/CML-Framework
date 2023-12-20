<?php
require_once __DIR__ . "/cml-load.php";

/** Validate Ajax requests */
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    http_response_code(403);
    die("Access denied");
}

/** Validate and sanitize */
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($action)) {
    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
}

/** Check if the action is set and callable */
if (empty($action) || !is_callable($action)) {
    http_response_code(404);
    die("Invalid action");
}

/** Attempt to call the function and handle errors */
try {
    call_user_func($action, $_REQUEST);
} catch (Exception $e) {
    error_log('Error calling function in file ' . __FILE__ . ' on line ' . __LINE__ . ': ' . $e->getMessage());
    http_response_code(500);
    die("Internal server error");
}
?>