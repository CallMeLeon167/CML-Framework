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

    // If the application is running in the production environment
    if (PRODUCTION === true) {
        // Turn off error reporting
        mysqli_report(MYSQLI_REPORT_OFF);
        error_reporting(0);
        ini_set('display_errors', 0);
    } else {
        // Enable error reporting in development or testing environment
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // If debug mode is enabled
        if (CML_DEBUG === true) {
            // Turn off error display and set a custom error handler
            ini_set('display_errors', 0);
            set_error_handler("customError");
        }
    }

    if (file_exists($errorfile)){
        ini_set('log_errors', 1);
        ini_set('error_log', $errorfile);
    }

    function customError($errno, $errstr, $errfile, $errline) {

        $errorTypes = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_NOTICE => 'Notice',
        ];
    
        $errorTypeString = $errorTypes[$errno] ?? 'Unknown Error Type';
        
        echo "
        <div style='
            background-color: #e74c3c;
            border: 1px solid #c0392b;
            border-radius: 10px;
            color: #fff;
            padding: 30px;
            margin: 30px;
            font-family: Roboto, sans-serif;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: left;
            '>
            <div style='    
            display: flex;
            justify-content: space-between;'>
            <h2 style='color: #fff; font-size: 28px; margin: 15px 0px;'>Error Details: $errorTypeString</h2>
            <div style='text-align: end;'>
            <span style='color: #ffffff70;font-size: 12px;'>Date/Time: " . date('Y-m-d H:i:s') . "</span><br>
            <span style='color: #ffffff70;font-size: 12px;'>PHP ".phpversion()."</span>
            </div>
            </div>
            <p><strong>Error:</strong> [$errno] ". htmlspecialchars($errstr) ."</p>
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
    
        $lines = file($errfile);
        $start = max(0, $errline - 5);
        $end = min(count($lines), $errline + 5);
    
        for ($i = $start; $i < $end; $i++) {
            echo "<span style='color: " . ($i == $errline - 1 ? '#e74c3c' : '#333') . ";'>"
                . "Line " . ($i + 1) . ": "
                . htmlspecialchars($lines[$i])
                . "</span>";
        }
    
        echo "</pre>";

        $trace = debug_backtrace(2);
        echo "<h3 style='color: #fff; font-size: 24px; margin-bottom: 15px;'>Stack Trace</h3>";
        foreach ($trace as $item) {
            if(isset($item['file']) && isset($item['line'])){
                echo "<p><strong>File:</strong> {$item['file']}</p>";
                echo "<p><strong>Line:</strong> {$item['line']}</p>";
                echo "<p><strong>Function:</strong> {$item['function']} ";
                if(isset($item['args'])){
                    foreach ($item['args'] as $args) {
                        echo basename($args);
                    }
                }
                echo "</p>";
                echo "<hr style='border-color: #c0392b; margin: 10px 0;'>";
            }
        }

        echo "</div>";
        return false;
    }
    
    // Trigger an error for testing
    echo $test;
?>