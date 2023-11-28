<?php 

require_once dirname(__DIR__, 2).'/vendor/autoload.php'; 

    /**
     * Loads configuration variables from the cml-config file.
     * Make sure to define necessary constants in cml-config.php.
     */
    if (file_exists($configPath = dirname(__DIR__) . '/config/cml-config.php')) {
        require_once $configPath;
    } else {
        // If the configuration file is missing, trigger a user error with a descriptive message.
        trigger_error('The cml-config.php file is missing. Please ensure it exists and contains the necessary configuration constants.', E_USER_ERROR);
    }

    /**
     * Loads functions from functions.php
     */
    if (file_exists($functions = dirname(__DIR__, 2).'/functions.php')){
        require_once $functions;
    }

    /**
     * Handles error configuration based on the environment.
     *
     * This function adjusts error reporting settings based on the environment,
     * controlling the display of errors and logging them to a specified file.
     * In the production environment, errors are suppressed for security and user experience reasons.
     * In other environments, all errors are displayed, aiding in development and debugging.
     */
    $errorfile = dirname(__DIR__, 2).ERRORLOG_FILE;

    if (PRODUCTION === true) {
        mysqli_report(MYSQLI_REPORT_OFF);
        error_reporting(0);
        ini_set('display_errors', 0);
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    if (file_exists($errorfile)){
        ini_set('log_errors', 1);
        ini_set('error_log', $errorfile);
    }

    // Enable error handling
    set_error_handler("customError");
    
    // Custom error function
    function customError($errno, $errstr, $errfile, $errline) {
        echo "
        <div style='
            background-color: #e74c3c;
            border: 1px solid #c0392b;
            border-radius: 10px;
            color: #fff;
            padding: 30px;
            margin: 30px;
            font-family: 'Roboto', sans-serif;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            margin: auto;
            text-align: left;
            '>
            <h2 style='color: #fff; font-size: 28px; margin-bottom: 15px;'>Error Details</h2>
            <p><strong>Error:</strong> [$errno] $errstr</p>
            <p><strong>File:</strong> $errfile</p>
            <p><strong>Line:</strong> $errline</p>
            <hr style='border-color: #c0392b; margin: 20px 0;'>
    
            <pre style='
                white-space: pre-wrap;
                background-color: #fff;
                color: #333;
                padding: 15px;
                border-radius: 10px;
                overflow-x: auto;
                '>";
    
        // Display code snippet
        $lines = file($errfile);
        $start = max(0, $errline - 5);
        $end = min(count($lines), $errline + 5);
    
        for ($i = $start; $i < $end; $i++) {
            echo "<span style='color: " . ($i == $errline - 1 ? '#e74c3c' : '#333') . ";'>"
                . "Line " . ($i + 1) . ": "
                . htmlspecialchars($lines[$i])
                . "</span>";
        }
    
        echo "</pre>
        </div>";
    
        // Pass error handling to PHP's default behavior
        return false;
    }
    
    // Trigger an error for testing
    echo $test;  // $test is not defined and will trigger an error
    
    ?>
    