<?php

// Check if the script is being run via the CLI
if (php_sapi_name() !== 'cli') {
    die("This script can only be executed via the command line (CLI).\n");
}

// Process CLI arguments
$scriptName = array_shift($argv);
$command = array_shift($argv);
$controllerName = array_shift($argv);

// Process optional parameters
$options = array_flip($argv);

// Check if the -db or --database parameter is present
$useDatabase = isset($options['--db']) || isset($options['--database']);

// Execute the corresponding command
switch ($command) {
    case 'create:controller':
        createController($controllerName, $useDatabase);
        break;
    default:
        echo "Unknown command\n";
        break;
}

// CLI command: php cli.php create:controller TestController --db
function createController($controllerName, $useDatabase) {
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
