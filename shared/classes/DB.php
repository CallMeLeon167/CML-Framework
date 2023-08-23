<?php 
namespace Classes;
class DB {
    private $conn;

    public function __construct() {
        $this->loadEnv(); // Lädt Umgebungsvariablen aus .env-Datei
        $this->connect(); // Stellt Verbindung zur Datenbank her
    }

    // Lädt Umgebungsvariablen aus .env-Datei
    private function loadEnv() {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ ."/../config");
        $dotenv->load();
    }

    // Stellt Verbindung zur Datenbank her
    private function connect() {
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
    }

    // Führt SQL-Abfrage aus und gibt Ergebnis als Array zurück
    public function sql2array(string $query, array $params = []) {
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
        $result = $stmt->get_result();
    
        if (!$result) {
            die("SQL Error: " . $stmt->error);
        }
    
        while ($row = $result->fetch_assoc()) {
            $sqlArray[] = $row;
        }
    
        $stmt->close();
        return $sqlArray;
    }

    public function sql2array_file(string $filename, array $params = []) {
        $sqlArray = [];
    
        // Read the contents of the SQL file
        $sqlContent = file_get_contents($filename);
    
        // Split SQL queries based on semicolons
        $queries = explode(';', $sqlContent);
    
        foreach ($queries as $query) {
            // Remove leading/trailing whitespace and skip empty queries
            $query = trim($query);
            if (!empty($query)) {
                // Execute the query and add result to the array
                $sqlArray = $this->sql2array($query, $params);
            }
        }
    
        return $sqlArray;
    }


    public function sql2db_file(string $filename, array $params = []) {
        // Read the contents of the SQL file
        $sqlContent = file_get_contents($filename);
    
        // Split SQL queries based on semicolons
        $queries = explode(';', $sqlContent);
    
        foreach ($queries as $query) {
            // Remove leading/trailing whitespace and skip empty queries
            $query = trim($query);
            if (!empty($query)) {
                $this->sql2db($query, $params);
            }
        }
    }

    // Führt SQL-Abfrage aus und gibt das Ergebnisobjekt zurück
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

    // Hilfsfunktion für bind_param
    private function refValues(array &$arr) {
        $refs = array();

        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }

        return $refs;
    }

    // Schließt die Datenbankverbindung
    public function close() {
        $this->conn->close();
    }
}

// // Beispiel 1: SELECT-Abfrage mit sql2array
// $query = "SELECT * FROM cml_code WHERE code_type = ?";
// $codeType = "example_type";
// $resultArray = $db->sql2array($query, [$codeType]);

// // Beispiel 2: INSERT-Abfrage mit sql2db
// $insertQuery = "INSERT INTO cml_code (code, code_share) VALUES (?, ?)";
// $code = "new_code";
// $codeShare = "share_value";
// $db->sql2db($insertQuery, [$code, $codeShare]);
?>