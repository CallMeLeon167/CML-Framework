<?php

/*
|--------------------------------------------------------------------------
| MySQL Settings
|--------------------------------------------------------------------------
|
| These settings define the configuration for connecting to the MySQL
| database. Provide a brief description for each setting below.
|
*/

/** 
 * The name of the MySQL database.
 */
define('DB_NAME', 'mydb');

/** 
 * MySQL database username for authentication.
 */
define('DB_USER', 'root');

/** 
 * MySQL database password for authentication.
 */
define('DB_PASSWORD', '');

/** 
 * MySQL hostname or IP address.
 */
define('DB_HOST', 'localhost');

/** 
 * Database Charset to use in creating database tables.
 * 'utf8' is a commonly used character set that supports a wide range of characters.
 */
define('DB_CHARSET', 'utf8mb4');

/*
|--------------------------------------------------------------------------
| Framework Settings
|--------------------------------------------------------------------------
|
| These settings define various configuration parameters for the framework.
| Provide a brief description for each setting below.
|
*/

/** 
 * Set the development mode.
 * When set to true, the application is in production mode; set to false for development.
 */
define('PRODUCTION', false);

/** 
 * Enable enhanced error display for better debugging.
 * Set to true to activate improved error messages.
 */
define('CML_DEBUG', true);

/** 
 * Define the path to the log file.
 * 
 * Set the value of 'LOG_FILE' to the absolute path of the log file you want to use.
 * This constant is typically used to specify where application logs should be written.
 * Make sure the specified path is writable by the web server process.
 * If you don't want to log information, leave the value as an empty string ('').
 */
define('ERRORLOG_FILE', '/errorlogfile.log');

/** 
 * The name of the application.
 * Replace 'MyApplication' with the actual name of your application.
 */
define('APP_NAME', 'MyApplication');

/** 
 * Path to CSS files.
 * Specify the directory path where CSS files are located.
 */
define('STYLE_PATH', 'web/css/');

/** 
 * Path to JavaScript files.
 * Specify the directory path where JavaScript files are located.
 */
define('SCRIPT_PATH', 'web/js/');

/** 
 * Path to SQL files.
 * Specify the directory path where SQL files are located.
 */
define('SQL_PATH', 'sql/');

/** 
 * Path to site files.
 * Specify the directory path where site-specific files are located.
 */
define('SITES_PATH', 'web/sites/');

/** 
 * Specify the folder path to the components.
 */
define('COMPONENTS_PATH', 'web/components/');
?>