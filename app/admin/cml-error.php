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

    ini_set('log_errors', 1);
    ini_set('error_log', $errorfile);

    /**
     * Custom error handler function.
     *
     * @param int    $errno   The level of the error raised.
     * @param string $errstr  The error message.
     * @param string $errfile The filename that the error was raised in.
     * @param int    $errline The line number the error was raised at.
     */
    function customError($errno, $errstr, $errfile, $errline) {
        global $errorfile;

        $errorTypes = [
            E_ERROR => 'Error',
            E_USER_ERROR  => 'Error',
            E_WARNING => 'Warning',
            E_NOTICE => 'Notice',
        ];
    
        $errorTypeString = $errorTypes[$errno] ?? 'Unknown Error Type';
        $id = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 20);
        $trace = debug_backtrace(2);
        if (file_exists($errorfile)){
            error_log("[ID: {$id}] ", 3, $errorfile);
        }
        
        ob_start();
        echo "
        <script>
            function closeOverlayById(id) {
                const overlay = document.querySelector('.' + id);
                if (overlay) {
                    overlay.style.display = 'none'
                }
            }
        
            document.addEventListener('click', function (event) {
                const closeOverlayBtn = event.target;
                const closeOverlayId = closeOverlayBtn.getAttribute('data-id');
        
                if (closeOverlayId && closeOverlayBtn.getAttribute('data-close-overlay') !== null) {
                    closeOverlayById(closeOverlayId);
                }
            });
        </script>

        <style>
        body, html{margin:0;padding:0;height: 100%;overflow-x:hidden;}
        .container{background-color:#E5E7EB;width: 100vw;font-family: Roboto, sans-serif;display: flex;flex-direction: column; color:black;max-height: 100vh;overflow: auto;}
        .error{background-color:#fff;margin:80px 80px 50px 80px;padding:30px;box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);display:flex;justify-content: space-between;border-radius: 6px;border: 1px solid #c7c7c7;}
        .error-versions{display: flex;flex-direction: column;align-items: flex-end;font-size:13px;color: #33333370;}
        .error-msg{margin:15px 0 0 0;}
        .stack-trace{width: 25%;}
        .error-file, .error-id{color: #575757;font-size: small;}
        .file-header{display: flex;flex-direction: column;align-items: flex-end;}
        .stack-trace h3{margin:10px}
        .stack-trace-data{display: flex;flex-direction: column;gap: 5px;font-size: 14px;padding: 10px;border-radius: 5px;}
        .error-type{padding: 5px 10px;margin: 10px;background-color: #ededed;border-radius:5px}
        .error-content{background-color:#fff;margin:0 80px 80px 80px;padding:20px;box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);display: flex;border-radius: 6px;border: 1px solid #c7c7c7;}
        .file{width: 100%;padding:20px;}
        .table-container {max-width: 100%;overflow-x: auto;margin-top: 10px;}
        .info-table {width: 100%;border-collapse: collapse;}
        .info-table th, .info-table td {border: 1px solid #ddd;padding: 8px;text-align: left;word-wrap: break-word;color: black;}
        .info-table td.value{word-break: break-word;}
        .empty-data {color: #888;font-style: italic;}
        .info-data{display: flex;align-items: center;gap: 10px;}
        .info-data h4{margin:10px 0;}
        .all-infos h3{margin: 0}
        .overlay {position: fixed;top: 0;left: 0;width: 100vw;height: 100vh;background: rgba(0, 0, 0, 0.5);display: flex;justify-content: center;align-items: center;z-index: 9999;overflow: auto;}
        .close-button {position: fixed;top: 10px;right: 30px;background-color: transparent;border: none;padding: 10px;cursor: pointer;font-size: 42px;color: #333;}
        .close-button:hover {color: #555;}
        hr{border-color: #ffffff; margin: 10px 0;}
        pre{white-space: pre-wrap;background-color: #f3f3f3;padding: 15px;overflow-x: auto;}
        pre span{display: inline-block; width: 100%; color: #444;}
        </style>

    <div class='overlay {$id}'>
    <button class='close-button' data-close-overlay data-id='{$id}'>×</button>
        <div class='container'>
            <div class='error'>
                <div class='error-info'>
                    <span class='error-type'>{$errorTypeString}</span>
                    <h2 class='error-msg'>". htmlspecialchars($errstr) ."</h2>
                    <span class='error-id'>Error-ID: {$id}</span> 
                </div>
                <div class='error-versions'>
                    <span>Date/Time: " . date('Y-m-d H:i:s') . "</span>
                    <span>CML Version: v".(new class { use CML\Classes\Functions\Functions; })::getFrameworkVersion()."</span>
                    <span>PHP ".phpversion()."</span>
                </div>
            </div>

            <div class='error-content'>
                <div class='stack-trace'>
                    <h3>Stack Trace</h3>";

                    // Loop through the stack trace and display relevant information
                    $firstIteration = true;
                    foreach ($trace as $item) {
                        if (isset($item['file']) && isset($item['line'])) {
                            echo "<div class='stack-trace-data'" . ($firstIteration ? ' style="background-color:#fd3a3a;color:white;"' : '') . ">";
                            echo "<span><strong>File:</strong>" . getFilePath($item['file']) . "</span>";
                            echo "<span><strong>Line:</strong> {$item['line']}</span>";

                            if (isset($item['args'])) {
                                foreach ($item['args'] as $args) {
                                    echo "<span><strong>{$item['function']}: </strong>" . basename($args);
                                }
                            } else {
                                echo "<span><strong>Function:</strong> {$item['function']} ";
                            }

                            echo "</div>";
                            echo "<hr>";

                            $firstIteration = false;
                        }
                    }

                echo "
                </div>

                <div class='file'>
                    <div class='file-header'>
                    <span class='error-file'>" . getFilePath($errfile) . ":{$errline}</span>
                    </div>
                    <div class='file-content'>
                    <pre><code class='language-php'>";
                    
                    // Display code snippet around the error line
                    $lines = file($errfile);
                    $start = max(0, $errline - 10);
                    $end = min(count($lines), $errline + 10);
    
                    for ($i = $start; $i < $end; $i++) {
                        echo "<span style='background-color: " . ($i == $errline - 1 ? '#FEDBDA' : '') . ";'>"
                            . ($i + 1) . ": "
                            . htmlspecialchars($lines[$i])
                            . "</span>";
                    }

                    echo "</code></pre>
                    </div>
                    <div class='all-infos'>
                    <hr>";

                    // Display various tables with data (GET, POST, FILES, SESSION, ENV, COOKIE, etc.)
                    generateTable($_GET, "Get Data");
                    generateTable($_POST, "Post Data");
                    generateTable($_FILES, "File Upload Data");
                    (new class { use CML\Classes\Functions\Session; })->startSession();
                    generateTable($_SESSION, "Session Data");
                    generateTable($_ENV, "Environment Variables");
                    generateTable($_COOKIE, "Cookie Data");
                    
                    generateTable(['HTTP Status' => http_response_code()], "HTTP Response");
                    generateTable($_SERVER, "Server Data");
                    generateTable(getallheaders(), "Header Information");

                    echo "
                    </div>
                </div>
            </div>
        </div>
    </div>";
    echo ob_get_clean();
    return false;
    }

    /**
     * Get relative file path from the document root.
     *
     * @param string $dir The absolute file path.
     * @return string The relative file path.
     */
    function getFilePath(string $dir):string {
        $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        return str_replace($documentRoot, '', str_replace('\\', '/', $dir));
    }

    /**
     * Generate a table for displaying key-value pairs.
     *
     * @param array  $data  The data to be displayed in the table.
     * @param string $title The title of the table.
     */
    function generateTable(array $data, string $title) {
        echo "<div class='table-container'>
                <div class='info-data'>
                <h4>$title</h4>";
        
        if (empty($data)) {
            echo "<span class='empty-data'>empty</span>
            </div>";
        } else {
            echo "
            </div>
            <table class='info-table'>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                    </tr>";
    
            foreach ($data as $key => $value) {
                echo "<tr>
                        <td>$key</td>
                        <td class='value'>$value</td>
                    </tr>";
            }
            echo "</table>";
        }
    
        echo "</div>";
    }
?>