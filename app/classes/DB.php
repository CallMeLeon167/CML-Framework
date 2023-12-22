<?php 
namespace CML\Classes;

/**
 * Class DB
 * 
 * The DB class provides methods for establishing a connection to a MySQL database, executing SQL queries, and performing database operations.
 *
 * @author CallMeLeon <kontakt@callmeleon.de>
 * @see https://docs.callmeleon.de/cml#db
 */
class DB {
    use Functions\Functions;

    /**
     * Stores MySQL connection.
     */
    private $conn;

    /**
     * Stores sql path.
     *
     * @var string
     */
    public string $sqlPath;
    
    /**
     * Constructor of the DB class. Calls the methods to load environment variables and establish a connection to the database.
     */
    public function __construct() {
        $this->sqlPath = SQL_PATH ?? '';
        $this->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    }

    /**
     * Destructor for the class. Automatically closes the database connection upon object destruction.
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Establishes a connection to the database.
     *
     * @param string $host The database host.
     * @param string $user The database username.
     * @param string $pass The database password.
     * @param string $dbname The database name.
     */
    public function connect(string $host, string $user, string $pass, string $dbname) {
        $this->conn = @new \mysqli($host, $user, $pass, $dbname);
        if ($this->conn->connect_error) {
            trigger_error("Connection failed! ".$this->conn->connect_error, E_USER_ERROR);
        }
        $this->conn->set_charset(DB_CHARSET);
    }

    /**
     * Connects to another database and closes the current connection if it's active.
     *
     * @param string $host The database host.
     * @param string $user The database username.
     * @param string $pass The database password.
     * @param string $dbname The database name.
     */
    public function connectToAnotherDB(string $host, string $user, string $pass, string $dbname) {
        if ($this->conn->ping()) {
            $this->close();
        }
        $this->connect($host, $user, $pass, $dbname);
    }

    /**
     * Restores the default database connection and closes the current connection if it's active.
     */
    public function defaultConnection() {
        if ($this->conn->ping()) {
            $this->close();
        }
        $this->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }

    /**
     * Executes an SQL query and returns the result as an array.
     *
     * @param string $query The SQL query.
     * @param array $params Parameters for the SQL query (optional).
     * @return array The result of the SQL query as an array.
     */
    public function sql2array(string $query, array $params = []):array {
        $sqlArray = array();
    
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
                    throw new \InvalidArgumentException("Ungültiger Parametertyp");
                }
                $values[] = $param;
            }
    
            array_unshift($values, $types);
    
            call_user_func_array(array($stmt, 'bind_param'), $this->refValues($values));
        }
    
        $stmt->execute();
        
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cleanedRow = array_map(function($value) {
                    return ($value !== null) ? htmlspecialchars($value) : null;
                }, $row);
        
                $sqlArray[] = $cleanedRow;
            }
        } else {
            throw new \Exception("SQL Error: " . $stmt->error);
        }
    
        $stmt->close();
        return $sqlArray;
    }

    /**
     * Executes an SQL query from a file and returns the result as an array.
     *
     * @param string $filename The filename of the SQL query.
     * @param array $params Parameters for the SQL query (optional).
     * @return array The result of the SQL query as an array.
     */

    public function sql2array_file(string $filename, array $params = []): array {
        $filepath = self::getRootPath($this->sqlPath . $filename);
        
        if (!file_exists($filepath)) {
            trigger_error("Could not find SQL file => '" . htmlentities($this->sqlPath . $filename) . "'", E_USER_ERROR);
        }
        
        $sqlContent = file_get_contents($filepath);
        $queries = explode(';', $sqlContent);

        return array_map(fn($query) => $this->sql2array(trim($query), $params), array_filter($queries));
    }
    
    /**
     * Executes an SQL query from a file and performs the operations in the database.
     *
     * @param string $filename The filename of the SQL query.
     * @param array $params Parameters for the SQL query (optional).
     */
    public function sql2db_file(string $filename, array $params = []) {
        $filepath = self::getRootPath($this->sqlPath . $filename);
    
        if (!file_exists($filepath)) {
            trigger_error("Could not find SQL file => '" . htmlentities($this->sqlPath . $filename) . "'", E_USER_ERROR);
        }
    
        $sqlContent = file_get_contents($filepath);
        $queries = array_filter(array_map('trim', explode(';', $sqlContent)));
    
        foreach ($queries as $query) {
            $this->sql2db($query, $params);
        }
    }
    

    /**
     * Executes an SQL query and performs the operations in the database.
     *
     * @param string $query The SQL query.
     * @param array $params Parameters for the SQL query (optional).
     */
    public function sql2db(string $query, array $params = []) {
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
    
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Executes an SQL query and returns the result as JSON.
     *
     * @param string $query The SQL query.
     * @param array $params Parameters for the SQL query (optional).
     * @return string The result of the SQL query as a JSON-encoded string.
     */
    public function sql2json(string $query, array $params = []):string {
        if (!empty($query)) {
            return json_encode($this->sql2array($query, $params));
        }
    }

    /**
     * Executes an SQL query from a file and returns the result as JSON.
     *
     * @param string $filename The filename of the SQL query.
     * @param array $params Parameters for the SQL query (optional).
     * @return string The result of the SQL query as a JSON-encoded string.
     */
    public function sql2json_file(string $filename, array $params = []):string {
        if (!empty($filename)) {
            return json_encode($this->sql2array_file($filename, $params));
        }
    }

    /**
     * Helper function for bind_param.
     *
     * @param array $arr An array to be referenced.
     * @return array An array of references.
     */
    private function refValues(array &$arr) {
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
    private function cleanInput(string $input):string {
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
    public function stringToHtml(string $string):string{
        return html_entity_decode(html_entity_decode($string));
    }

    /**
     * Closes the database connection.
     */
    public function close() {
        $this->conn->close();
    }
}
?>