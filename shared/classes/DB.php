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

    public function sql2array_file(string $filename, array $params = []) {
        $sqlArray = [];
    
        // Read the contents of the SQL file
        $sqlContent = file_get_contents(dirname(__DIR__).'/sql/'.ltrim($filename, "/"));
    
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
        $sqlContent = file_get_contents(dirname(__DIR__).'/sql/'.ltrim($filename, "/"));
    
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

    public function sql2json(string $query, array $params = []) {
        if (!empty($query)) {
            // Execute the query and add result to the array
            return json_encode($this->sql2array($query, $params));
        }

    }
    public function sql2json_file(string $filename, array $params = []) {
        if (!empty($filename)) {
            // Execute the query and add result to the array
            return json_encode($this->sql2array_file($filename, $params));
        }
    }

    // Hilfsfunktion für bind_param
    private function refValues(array &$arr) {
        $refs = array();

        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }

        return $refs;
    }

    private function cleanInput($input) {
        // Entfernen von potenziell schädlichen Zeichen
        $input = trim($input); // Leerzeichen am Anfang und Ende entfernen
        $input = stripslashes($input); // Backslashes entfernen
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); // HTML-Sonderzeichen umwandeln

        return $input;
    }

    public function stringToHtml(string $string){
        return html_entity_decode(html_entity_decode($string));
    }

    // Schließt die Datenbankverbindung
    public function close() {
        $this->conn->close();
    }
}
?>