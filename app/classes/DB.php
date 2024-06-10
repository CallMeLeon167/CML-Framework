<?php

namespace CML\Classes;

use Exception;
use mysqli;

/**
 * Class DB
 *
 * The DB class provides methods for establishing a connection to a MySQL database, executing SQL queries, and performing database operations.
 *
 * @author CallMeLeon <kontakt@callmeleon.de>
 * @see https://docs.callmeleon.de/database
 */
class DB
{
    use Functions\Functions;

    /**
     * Stores MySQL connection.
     */
    private mysqli $conn;


    /**
     * Indicates whether the database connection is established or not.
     *
     * @var bool
     */
    public bool $connected = false;

    /**
     * Stores sql path.
     *
     * @var string
     */
    public string $sqlPath = "";

    /**
     * Constructor of the DB class. Calls the methods to load environment variables and establish a connection to the database.
     */
    public function __construct(bool $autoConnect = true, private readonly bool $autoClose = false)
    {
        $this->sqlPath = cml_config('SQL_PATH');
        if ($autoConnect) {
            $this->connected = true;
            $this->connect(cml_config('DB_HOST'), cml_config('DB_USER'), cml_config('DB_PASSWORD'), cml_config('DB_NAME'));
        }
    }

    public function __destruct()
    {
        if ($this->autoClose === true) {
            $this->close();
        }
    }

    /**
     * Establishes a connection to the database.
     *
     * @param string $host The database host.
     * @param string $user The database username.
     * @param string $pass The database password.
     * @param string $dbName The database name.
     */
    public function connect(string $host, string $user, string $pass, string $dbName): void
    {
        $this->conn = @new mysqli($host, $user, $pass, $dbName);
        if ($this->conn->connect_error) {
            trigger_error("Connection failed! " . $this->conn->connect_error, E_USER_ERROR);
        }
        $this->conn->set_charset(cml_config('DB_CHARSET'));
        $this->connected = true;
    }

    /**
     * Connects to another database and closes the current connection if it's active.
     *
     * @param string $host The database host.
     * @param string $user The database username.
     * @param string $pass The database password.
     * @param string $dbname The database name.
     */
    public function connectToAnotherDB(string $host, string $user, string $pass, string $dbName): void
    {
        if ($this->conn->ping()) {
            $this->close();
        }
        $this->connect($host, $user, $pass, $dbName);
    }

    /**
     * Restores the default database connection and closes the current connection if it's active.
     */
    public function defaultConnection(): void
    {
        if ($this->conn->ping()) {
            $this->close();
        }
        $this->connect(cml_config('DB_HOST'), cml_config('DB_USER'), cml_config('DB_PASSWORD'), cml_config('DB_NAME'));
    }

    /**
     * Retrieves the file and line number where a specific function is called from.
     *
     * @param string $functionName The name of the function to trace.
     * @return array|null An array containing the file and line number where the function is called from,
     *                    or null if the function is not found in the call stack.
     */
    protected function _traceFunctionCalls(string $functionName): ?array
    {
        $trace = debug_backtrace();
        foreach ($trace as $caller) {
            if (isset($caller['function']) && $caller['function'] === $functionName && isset($caller['file'])) {
                $file = str_replace(rtrim(self::getRootPath(), "/"), '', $caller['file']);
                $line = $caller['line'];
                return ['file' => $file, 'line' => $line];
            }
        }
    }

    /**
     * Executes an SQL query and returns the result as an array.
     *
     * @param string $query The SQL query.
     * @param array $params Parameters for the SQL query (optional).
     * @return array The result of the SQL query as an array.
     *
     * @throws Exception
     */
    public function sql2array(string $query, array $params = []): array
    {
        if (!$this->connected) {
            trigger_error("No database connected!", E_USER_ERROR);
        }

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            trigger_error("SQL Error: " . $this->conn->error, E_USER_ERROR);
        }

        if (!empty($params)) {
            $types = "";
            $values = [];

            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= "i";
                } elseif (is_string($param)) {
                    $types .= "s";
                    $param = filter_var($param, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                } elseif (is_double($param)) {
                    $types .= "d";
                } else {
                    throw new \InvalidArgumentException("Invalid Parametertype");
                }
                $values[] = $param;
            }

            array_unshift($values, $types);

            call_user_func_array(array($stmt, 'bind_param'), $this->refValues($values));
        }

        $startTime = microtime(true);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result) {
            $sqlArray = [];
            while ($row = $result->fetch_assoc()) {
                $cleanedRow = array_map(function ($value) {
                    return ($value !== null) ? htmlspecialchars($value) : null;
                }, $row);

                $sqlArray[] = $cleanedRow;
            }
        } else {
            throw new Exception("SQL Error: " . $stmt->error);
        }
        $executionTime = microtime(true) - $startTime;
        $executionTime = round($executionTime < 1 ? $executionTime * 1000 : $executionTime, $executionTime < 1 ? 0 : 2) . ($executionTime < 1 ? ' ms' : ' s');

        $callData = $this->_traceFunctionCalls('sql2array');

        $this->cml_db_update_amount();
        $this->cml_db_update_request_query(array_merge(['query' => $query, 'params' => $params, 'executionTime' => $executionTime], $callData));

        $stmt->close();
        return $sqlArray;
    }

    /**
     * Executes an SQL query from a file and returns the result as an array.
     *
     * @param string $fileName The filename of the SQL query.
     * @param array $params Parameters for the SQL query (optional).
     * @return array The result of the SQL query as an array.
     *
     * @throws Exception
     */

    public function sqlFile2array(string $fileName, array $params = []): array
    {
        $filepath = self::getRootPath($this->sqlPath . $fileName);

        if (!file_exists($filepath)) {
            trigger_error("Could not find SQL file => '" . htmlentities($this->sqlPath . $fileName) . "'", E_USER_ERROR);
        }

        $sqlContent = file_get_contents($filepath);
        $queries = explode(';', $sqlContent);

        return array_map(fn ($query) => $this->sql2array(trim($query), $params), array_filter($queries));
    }

    /**
     * Executes an SQL query from a file and performs the operations in the database.
     *
     * @param string $fileName The filename of the SQL query.
     * @param array $params Parameters for the SQL query (optional).
     */
    public function sqlFile2db(string $fileName, array $params = []): void
    {
        $filepath = self::getRootPath($this->sqlPath . $fileName);

        if (!file_exists($filepath)) {
            trigger_error("Could not find SQL file => '" . htmlentities($this->sqlPath . $fileName) . "'", E_USER_ERROR);
        }

        $sqlContent = file_get_contents($filepath);
        $queries = array_filter(array_map('trim', explode(';', $sqlContent)));

        foreach ($queries as $query) {
            $this->sql2db($query, $params);
        }
    }


    /**
     * Executes a SQL query on the database and returns the number of affected rows.
     *
     * @param string $query The SQL query to execute.
     * @param array $params An optional array of parameters to bind to the query.
     * @return array The number of affected rows.
     */
    public function sql2db(string $query, array $params = []): array
    {
        if (!$this->connected) {
            trigger_error("No database connected!", E_USER_ERROR);
        }

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            trigger_error("SQL Error: " . $this->conn->error, E_USER_ERROR);
        }

        if (!empty($params)) {
            $types = "";
            $values = [];

            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= "i";
                } elseif (is_string($param)) {
                    $types .= "s";
                    $param = $this->cleanInput($param);
                } elseif (is_double($param)) {
                    $types .= "d";
                } else {
                    $types .= "s";
                }
                $values[] = $param;
            }

            array_unshift($values, $types);

            call_user_func_array(array($stmt, 'bind_param'), $this->refValues($values));
        }

        $startTime = microtime(true);
        $stmt->execute();
        $executionTime = microtime(true) - $startTime;
        $executionTime = round($executionTime < 1 ? $executionTime * 1000 : $executionTime, $executionTime < 1 ? 0 : 2) . ($executionTime < 1 ? ' ms' : ' s');

        $affectedRows = $stmt->affected_rows;
        $insertId = $stmt->insert_id;

        $callData = $this->_traceFunctionCalls('sql2db');

        $this->cml_db_update_amount();
        $this->cml_db_update_request_query(array_merge(['query' => $query, 'params' => $params, 'affected_rows' => $affectedRows, 'executionTime' => $executionTime], $callData));

        $stmt->close();
        return [
            "affectedRows" => $affectedRows,
            "insertId" => $insertId
        ];
    }

    /**
     * Increases the value of the global variable $cml_db_request_amount by 1.
     */
    private function cml_db_update_amount()
    {
        global $cml_db_request_amount;
        $cml_db_request_amount++;
    }

    /**
     * Updates the request query data in the cml_db_request_query array.
     *
     * @param array $queryData The query data to be added to the cml_db_request_query array.
     */
    private function cml_db_update_request_query(array $queryData)
    {
        global $cml_db_request_query;
        $cml_db_request_query[] = $queryData;
    }


    /**
     * Executes an SQL query and returns the result as JSON.
     *
     * @param string $query The SQL query.
     * @param array $params Parameters for the SQL query (optional).
     * @return string|bool The result of the SQL query as a JSON-encoded string.
     * @throws Exception
     */
    public function sql2json(string $query, array $params = []): string|bool
    {
        if (!empty($query)) {
            return json_encode($this->sql2array($query, $params));
        }

        return false; //temporary stopgap
    }

    /**
     * Executes an SQL query from a file and returns the result as JSON.
     *
     * @param string $fileName The filename of the SQL query.
     * @param array $params Parameters for the SQL query (optional).
     * @return string|bool The result of the SQL query as a JSON-encoded string.
     * @throws Exception
     */
    public function sqlFile2json(string $fileName, array $params = []): string|bool
    {
        if (!empty($fileName)) {
            return json_encode($this->sqlFile2array($fileName, $params));
        }

        return false; //temporary stopgap
    }


    /**
     * Creates a database dump and saves it to a file.
     *
     * @param string $dumpFile The path to the dump file.
     * @param bool $insertData Whether to include table data in the dump.
     * @param bool $onlyInserts Whether to include only INSERT statements in the dump.
     * @param bool $dropTables Whether to include DROP TABLE statements in the dump.
     * @return bool Returns true if the dump was created successfully, false otherwise.
     */
    public function createDatabaseDump(string $dumpFile, bool $insertData = true, bool $onlyInserts = false, bool $dropTables = true): bool
    {
        if (!$this->connected) {
            trigger_error("No database connection established.", E_USER_WARNING);
            return false;
        }

        // Open dump file
        $fp = fopen($dumpFile, 'w');
        if (!$fp) {
            trigger_error("Failed to open dump file.", E_USER_WARNING);
            return false;
        }

        // Get database name
        $database = $this->conn->real_escape_string($this->conn->query("SELECT DATABASE()")->fetch_row()[0]);
        if (empty($database)) {
            trigger_error("No database selected.", E_USER_WARNING);
            return false;
        }

        // Write general information to dump file
        $output = "-- Database dump for database: $database --\n" .
            "-- Created on: " . date('Y-m-d H:i:s') . " --\n\n";
        fwrite($fp, $output);

        if (php_sapi_name() == 'cli') {
            echo "\033[2J"; // clear screen
            echo "\033[0;0H"; // set cursors to the beginning
            echo $output;
        }

        // Get all tables from database
        $tables = [];
        $sql = "SHOW TABLES";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tableName = $row['Tables_in_' . $database];
                $tables[] = $tableName;
                if (php_sapi_name() == 'cli') {
                    echo "Saving table: $tableName\n";
                }
            }
        }

        // If desired, drop existing tables
        if ($dropTables && !$onlyInserts) {
            foreach ($tables as $table) {
                fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
            }
            fwrite($fp, "\n");
        }

        // Write table structure and data to dump file
        $count = count($tables) - 1;
        foreach ($tables as $i => $table) {
            if (php_sapi_name() == 'cli') {
                showProgress($i, $count);
            }
            if (!$onlyInserts) {
                $sql = "SHOW CREATE TABLE `$table`";
                $result = $this->conn->query($sql);
                $row = $result->fetch_assoc();
                fwrite($fp, "/* Table structure for table `$table` */\n\n");
                fwrite($fp, $row['Create Table'] . ";\n\n");
            }

            if ($insertData) {
                $sql = "SELECT * FROM `$table`";
                $result = $this->conn->query($sql);
                if ($result->num_rows > 0) {
                    fwrite($fp, "/* Data for table `$table` */\n\n");
                    while ($row = $result->fetch_assoc()) {
                        $insert = "INSERT INTO `$table` VALUES (";
                        foreach ($row as $field) {
                            $insert .= "'" . ($field !== null ? $this->conn->real_escape_string($field) : 'NULL') . "',";
                        }
                        // Remove trailing comma
                        $insert = rtrim($insert, ',');
                        $insert .= ");\n";
                        fwrite($fp, $insert);
                    }
                    fwrite($fp, "\n");
                }
            }
        }

        if (php_sapi_name() == 'cli') {
            echo "\n\nSQL Dump finished! Saved {$dumpFile}\n";
        }
        fclose($fp);
        return true;
    }

    /**
     * Checks if the 'cml_data' table exists in the database. If not, creates the table.
     *
     * @return bool Returns true if the table exists, false if it needs to be created.
     *
     * @throws Exception
     */
    protected function checkDataTable(): bool
    {
        $result = $this->sql2array("SHOW TABLES LIKE 'cml_data'");
        if (!$result) {
            $this->sql2db("CREATE TABLE `cml_data` (
                `data_id` INT(11) NOT NULL AUTO_INCREMENT,
                `data_name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                `data_value` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                `data_created` DATETIME NULL DEFAULT NULL,
                `data_lastModify` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`data_id`) USING BTREE,
                UNIQUE INDEX `data_name` (`data_name`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;");
            return false;
        }
        return true;
    }

    /**
     * Sets data in the database.
     *
     * @param string $name The name of the data.
     * @param string $value The value of the data.
     * @param bool $htmlSpecialChars Whether to apply htmlspecialchars to the value or not.
     *
     * @throws Exception
     */
    public function setData(string $name, string $value, bool $htmlSpecialChars = false): void
    {
        $this->checkDataTable();
        $query = "INSERT INTO `cml_data` (`data_name`, `data_value`, `data_created`)
                  VALUES ('$name', '$value', now())
                  ON DUPLICATE KEY UPDATE `data_value` = " . ($htmlSpecialChars ? '?' : "'$value'") . ", `data_lastModify` = now()";
        $this->sql2db($query, $htmlSpecialChars ? [$value] : []);
    }

    /**
     * Retrieves data from the database based on the given name.
     *
     * @param string $name The name of the data to retrieve.
     * @return mixed The value of the data if found, or false if not found.
     *
     * @throws Exception
     */
    public function getData(string $name): mixed
    {
        $this->checkDataTable();
        $result = $this->sql2array("SELECT `data_value` FROM `cml_data` WHERE `data_name` = '$name'");
        return $result[0]['data_value'] ?? false;
    }

    /**
     * Deletes data from the database based on the given name.
     *
     * @param string $name The name of the data to be deleted.
     */
    public function deleteData(string $name): int
    {
        $this->checkDataTable();
        return $this->sql2db("DELETE FROM `cml_data` WHERE `data_name` = '$name'");
    }

    /**
     * Helper function for bind_param.
     *
     * @param array $arr An array to be referenced.
     * @return array An array of references.
     */
    private function refValues(array &$arr)
    {
        $refs = array();

        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }

        return $refs;
    }

    /**
     * Cleans input data to prevent potential security issues.
     *
     * @param string $input The input data to be cleaned.
     * @return string The cleaned input data.
     */
    private function cleanInput(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Decodes HTML entities in a string.
     *
     * @param string $string The input string with HTML entities.
     * @return string The decoded HTML string.
     */
    public function stringToHtml(string $string): string
    {
        return html_entity_decode(html_entity_decode($string));
    }

    /**
     * Closes the database connection.
     */
    public function close(): void
    {
        if ($this->connected) {
            $this->connected = false;
            $this->conn->close();
        }
    }
}
