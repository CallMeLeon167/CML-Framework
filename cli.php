<?php

// Check if the script is being run via the CLI
if (php_sapi_name() !== 'cli') {
    die("This script can only be executed via the command line (CLI).\n");
}

// The constant 'CLI' is used to indicate that the script is being executed in a command-line interface (CLI) environment.
define('CLI', true);

// Include cml-load.php file
error_reporting(E_ALL & ~E_NOTICE);
include_once 'app/admin/cml-load.php';

// Process CLI arguments
$scriptName = array_shift($argv);
$command = array_shift($argv);

// Process optional parameters
$options = array_flip($argv);


// Execute the corresponding command
switch ($command) {
    case 'create:controller':
        $useDatabase = isset($options['--db']) || isset($options['--database']);
        $controllerName = array_shift($argv);
        createController($controllerName, $useDatabase);
        break;

    case 'config':
        createConfig();
        break;

    case 'create:dump':
        $noInsert = isset($options['--no-insert']);
        $onlyInserts = isset($options['--only-insert']);
        $noDrop = isset($options['--no-drop']);

        $fileName = array_shift($argv) ?? "SQL_DUMP_" . date('Y-m-d_H:i:s') . ".sql";

        $db = new CML\Classes\DB();
        $db->createDatabaseDump($fileName, !$noInsert, $onlyInserts, !$noDrop);
        break;

    case 'cml:version':
        version();
        break;

    case 'cml:update':
        if ($checkUpdate = isset($options['--check'])) {
            checkUpdate($checkUpdate);
        } else {
            updateCML();
            updateFiles();
            echo "\nUpdate complete!\n\n";
            echo "Your now on Version: v" . useTrait('getFrameworkVersion');
        }
        break;

    case 'cml:component':
        downloadComponent($argv);
        break;

    case 'help':
        help();
        break;

    case 'do:action':
        do_action($argv);
        break;

    default:
        echo "Unknown command\n";
        help();
        break;
}

// CLI command: php cli.php create:controller TestController --db
function createController($controllerName, $useDatabase)
{
    $controllerFilePath = __DIR__ . "/controllers/{$controllerName}.php";

    // Check if the file already exists
    if (file_exists($controllerFilePath)) {
        echo "The controller {$controllerName} already exists. Choose a different name.\n";
        return;
    }

    // Create the controller code based on the presence of the -db or --database parameter
    if ($useDatabase) {
        // Create the controller with database code
        $controllerCode = "<?php

namespace CML\Controllers;

use CML\Classes\DB;

class {$controllerName} extends DB {
    public function getTest(\$params) {
        
        // \$arrID = ['id' => \$params['id']];
        // \$news = DB::sql2array(\"SELECT * FROM news\");
        // return \$news;
        
        // Write your logic here
    }
}";
    } else {
        // Create the controller without database code
        $controllerCode = "<?php

namespace CML\Controllers;

class {$controllerName} {
    public function myFirstController(\$params) {
        // Write your logic here
    }
}";
    }

    // Write the controller code to the file
    if (file_put_contents($controllerFilePath, $controllerCode) !== false) {
        echo "Controller {$controllerName} created: {$controllerFilePath}\n";
    } else {
        echo "Error creating the controller\n";
    }
}

function createConfig()
{
    $configPath = useTrait('getRootPath') . 'app/config/';
    $templateFile = $configPath . 'cml-config.template.php';
    $newConfigFile = $configPath . 'cml-config.php';

    if (!file_exists($templateFile)) {
        echo "Error: Template file 'cml-config.template.php' not found in {$configPath}\n";
        return;
    }

    if (file_exists($newConfigFile)) {
        echo "Warning: 'cml-config.php' already exists. Do you want to overwrite it? (y/n): ";
        $answer = trim(fgets(STDIN));
        if (strtolower($answer) !== 'y') {
            echo "Operation cancelled.\n";
            return;
        }
    }

    $configContent = file_get_contents($templateFile);

    echo "Do you want to set up the configuration interactively? (y/n): ";
    $setupInteractively = strtolower(trim(fgets(STDIN))) === 'y';

    if ($setupInteractively) {
        $configContent = setupConfigInteractively($configContent);
    }

    if (file_put_contents($newConfigFile, $configContent)) {
        echo "Config file created successfully: {$newConfigFile}\n";
    } else {
        echo "Error: Failed to create config file.\n";
    }
}

function setupConfigInteractively($configContent)
{
    $constants = [
        'DB_NAME' => 'Enter the name of the MySQL database (default: mydb): ',
        'DB_USER' => 'Enter the MySQL database username (default: root): ',
        'DB_PASSWORD' => 'Enter the MySQL database password (default: ): ',
        'DB_HOST' => 'Enter the MySQL hostname or IP address (default: localhost): ',
        'DB_CHARSET' => 'Enter the database charset (default: utf8mb4): ',
        'PRODUCTION' => 'Is this a production environment? (default: false) (true/false): ',
        'NONCE_KEY' => 'Enter a secret key for NONCE_KEY (default: [generated string]): ',
        'CML_DEBUG' => 'Enable enhanced error display? (default: true) (true/false): ',
        'CML_DEBUG_BAR' => 'Enable debug bar? (default: false) (true/false): ',
        'ERRORLOG_FILE' => 'Enter the path to the error log file (default: /errorlogfile.log): ',
        'APP_NAME' => 'Enter the name of your application (default: MyApplication): ',
    ];

    foreach ($constants as $constant => $prompt) {
        echo $prompt;
        $value = trim(fgets(STDIN));

        if ($value === '') {
            switch ($constant) {
                case 'NONCE_KEY':
                    $value = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{};:,.<>?'), 0, 30);
                    break;
                default:
                    continue 2;
            }
        }

        if (in_array($constant, ['PRODUCTION', 'CML_DEBUG', 'CML_DEBUG_BAR'])) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value === null) {
                echo "Invalid input. Please enter 'true' or 'false'. Using default value.\n";
                $value = false;
            }
        }

        $pattern = "/define\('$constant',\s*(.*?)\);/";
        if (is_bool($value)) {
            $replacement = "define('$constant', " . ($value ? 'true' : 'false') . ");";
        } else {
            $replacement = "define('$constant', '$value');";
        }
        $configContent = preg_replace($pattern, $replacement, $configContent);
    }

    return $configContent;
}

function version()
{
    $version = "v" . useTrait('getFrameworkVersion');
    echo "CML Framework Version: {$version}\n";
}

function updateCML($url = "https://api.github.com/repos/CallMeLeon167/CML-Framework/contents/app", $path = __DIR__ . '/app')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $response = curl_exec($ch);
    curl_close($ch);

    $files = json_decode($response);

    // Loop through each file in the response
    $directories = [];
    foreach ($files as $file) {
        // If it's a file, download it
        if ($file->type == 'file') {
            file_put_contents($path . '/' . $file->name, file_get_contents($file->download_url));
        }
        // If it's a directory, create it if it doesn't exist
        elseif ($file->type == 'dir') {
            $directories[] = $file;
            if (!file_exists($path . '/' . $file->name)) {
                mkdir($path . '/' . $file->name, 0777, true);
            }
        }
    }

    // Recursively call the function for each subdirectory
    foreach ($directories as $dir) {
        updateCML($dir->url, $path . '/' . $dir->name);
        echo "Update " . $dir->name . " complete!\n";
    }
}

function updateFiles()
{
    $rootUrl = 'https://api.github.com/repos/CallMeLeon167/CML-Framework/contents/';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $rootUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response);

    if ($response) {
        $filesToUpdate = ['cli.php', '.htaccess', 'robots.txt', '.gitignore'];

        foreach ($filesToUpdate as $file_name) {
            $key = array_search($file_name, array_column($response, 'name'));
            if ($key !== false) {
                $file_object = $response[$key];
                $fileUrl = $file_object->download_url;
                file_put_contents(__DIR__ . '/' . $file_name, file_get_contents($fileUrl));
                echo "Update " . $file_object->name . " complete!\n";
            } else {
                echo "File " . $file_name . " not found in the repository.\n";
            }
        }
    }
}

function checkUpdate($checkUpdate)
{
    if ($checkUpdate) {
        $localVersion = "v" . useTrait('getFrameworkVersion');
        $response = @json_decode(file_get_contents(
            "https://api.github.com/repos/CallMeLeon167/CML-Framework",
            false,
            stream_context_create([
                "http" => [
                    "header" => [
                        "User-Agent: Mozilla/5.0",
                    ]
                ]
            ])
        ));
        $remoteVerions = $response ? ($response->default_branch ?? "Error: Unable to retrieve default branch information.") : "Error: Unable to retrieve data from GitHub API.";
        if (strpos($remoteVerions, 'Error') !== false) {
            echo "Unable to check for updates. Please try again later.\n";
            return;
        }

        // Compare version parts sequentially
        if ($remoteVerions == $localVersion) {
            echo "Your CML Framework is up to date. Version: {$localVersion}\n";
        } else {
            echo "CML Framework {$remoteVerions} is available. Your Version: {$localVersion}\n";
        }
    }
}

function do_action($options)
{
    if (!isset($options[0])) {
        echo "No action provided.\n";
        return;
    }
    $action = $options[0];
    unset($options[0]);
    try {
        call_user_func_array($action, $options);
        echo "\nAction: $action Called successfully!\n";
    } catch (\Throwable $th) {
        echo $th;
    }
}

function downloadComponent($options)
{
    if (!isset($options[0])) {
        echo "No component provided.\n";
        echo "cml:component [component_name]\n";
        return;
    }

    $path = useTrait('getRootPath', cml_config('COMPONENTS_PATH'));
    $url = 'https://docs.callmeleon.de/download/' . $options[0];

    $filename = $options[0] . '.cml.php';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (empty($response)) {
        echo 'This Component is not available: "' . $options[0] . '"';
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 200) {
            $pfad = $path . $filename;
            file_put_contents($pfad, $response);
            echo 'Component download was successfully and saved in: ' . $pfad;
        } else {
            echo 'Error: (HTTP-Code ' . $http_code . ')';
        }
    }
    curl_close($ch);
}

function help()
{
    echo "Usage: php cli.php [command] [options]\n\n";
    echo "Available commands:\n";
    echo "  help \t\t\t\t\t\t\t\t\tShows CML Framework command list\n";
    echo "  config\t\t\t\t\t\t\t\tCreate a new config file from template\n";
    echo "  create:controller [ControllerName] [--db|--database]\t\t\tCreate a new controller\n";
    echo "  create:dump [FileName] [--no-insert] [--only-insert] [--no-drop]\tCreate a database dump\n";
    echo "  cml:version\t\t\t\t\t\t\t\tShow CML Framework version\n";
    echo "  cml:update [--check]\t\t\t\t\t\t\tUpdate CML Framework\n";
    echo "  cml:component [component name]\t\t\t\t\tDownload an official CML Framework Component\n";
    echo "  do:action [function name] [...params]\t\t\t\t\tCall a function from the functions.php\n";

    echo "\nOptions:\n";
    echo "  --db, --database\t\t\t\t\t\t\tGenerate controller with database code\n";
    echo "  --no-insert\t\t\t\t\t\t\t\tExclude INSERT statements from database dump\n";
    echo "  --only-insert\t\t\t\t\t\t\t\tOnly include INSERT statements in database dump\n";
    echo "  --no-drop\t\t\t\t\t\t\t\tExclude DROP TABLE statements from database dump\n";
    echo "  --check\t\t\t\t\t\t\t\tCheck for available updates\n";
    echo "\nExample:\n";
    echo "  php cli.php create:controller TestController --db\n";
}

function showProgress($currentIteration, $totalIterations)
{
    $percentage = round(($currentIteration / $totalIterations) * 100);
    $barLength = 20;
    $barFill = round($percentage / 100 * $barLength);
    $barEmpty = $barLength - $barFill;
    echo "\r[" . str_repeat("=", $barFill) . str_repeat(" ", $barEmpty) . "] {$percentage}%   ";
}
