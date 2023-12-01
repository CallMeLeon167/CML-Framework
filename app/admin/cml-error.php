<?php 

    /**
     * Handles error configuration based on the environment.
     *
     * This function adjusts error reporting settings based on the environment,
     * controlling the display of errors and logging them to a specified file.
     * In the production environment, errors are suppressed for security and user experience reasons.
     * In other environments, all errors are displayed, aiding in development and debugging.
     */
    $errorfile = dirname(__DIR__, 2).ERRORLOG_FILE;

    // Turn off error reporting in production environment, enable otherwise
    error_reporting(PRODUCTION ? 0 : E_ALL);
    ini_set('display_errors', PRODUCTION || (CML_DEBUG && !PRODUCTION) ? 0 : 1);

    // If debug mode is enabled, set a custom error handler
    if (CML_DEBUG && !PRODUCTION) {
        set_error_handler("customError");
    } else {
        // Turn off error reporting when not in debug mode
        mysqli_report(PRODUCTION ? MYSQLI_REPORT_OFF : MYSQLI_REPORT_ERROR);
    }

    if (file_exists($errorfile)){
        ini_set('log_errors', 1);
        ini_set('error_log', $errorfile);
    }

    function customError($errno, $errstr, $errfile, $errline) {

        $errorTypes = [
            E_ERROR => 'Error',
            E_USER_ERROR  => 'Error',
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
            box-shadow: 0 8px 16px rgb(0 0 0 / 42%);
            text-align: left;
            '>
            <div style='    
            display: flex;
            justify-content: space-between;'>
            <h2 style='color: #fff; font-size: 28px; margin: 15px 0px;'>Error Details: $errorTypeString</h2>
            <div style='text-align: end;'>
            <span style='color: #ffffff70;font-size: 12px;'>Date/Time: " . date('Y-m-d H:i:s') . "</span><br>
            <span style='color: #ffffff70;font-size: 12px;'>CML Version: v".(new class { use CML\Classes\Functions\Functions; })::getFrameworkVersion()."</span> <br>
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