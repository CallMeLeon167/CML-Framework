<?php 
namespace Classes;

/**
 * The DB class establishes a connection to the database and allows for executing SQL queries.
 */
class DB {
    private $conn;

    /**
     * Constructor of the DB class. Calls the methods to load environment variables and establish a connection to the database.
     */
    public function __construct() {
        $this->loadEnv();
        $this->connect(); 
    }

    /**
     * Loads environment variables from the .env file.
     */
    private function loadEnv() {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ ."/../config");
        $dotenv->load();
    }

    /**
     * Establishes a connection to the database.
     */
    public function connect() {
        $host = $_ENV['DB_HOST'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $dbname = $_ENV['DB_NAME'];

        if($_SERVER['HTTP_HOST'] != 'localhost') {
            mysqli_report(MYSQLI_REPORT_OFF);
        }

        $this->conn = @new \mysqli($host, $user, $pass, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed!");
        }

        $this->conn->set_charset("utf8mb4");
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
            die("SQL Error: " . $this->conn->error);
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
                $cleanedRow = array_map('htmlspecialchars', $row);
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
    public function sql2array_file(string $filename, array $params = []):array {
        $sqlArray = [];
        $sqlContent = file_get_contents(dirname(__DIR__).'/sql/'.ltrim($filename, "/"));
        $queries = explode(';', $sqlContent);
    
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $sqlArray = $this->sql2array($query, $params);
            }
        }

        return $sqlArray;
    }

    /**
     * Executes an SQL query from a file and performs the operations in the database.
     *
     * @param string $filename The filename of the SQL query.
     * @param array $params Parameters for the SQL query (optional).
     */
    public function sql2db_file(string $filename, array $params = []) {
        $sqlContent = file_get_contents(dirname(__DIR__).'/sql/'.ltrim($filename, "/"));
    
        $queries = explode(';', $sqlContent);
    
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $this->sql2db($query, $params);
            }
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
            die("SQL Error: " . $this->conn->error);
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
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); 

        return $input;
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