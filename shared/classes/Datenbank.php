<?php 
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

        $this->conn = @new mysqli($host, $user, $pass, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed!");
        }
    }

    // Führt SQL-Abfrage aus und gibt Ergebnis als Array zurück
    public function sql2array(string $query) {
        $sqlArray = array();
        $result = $this->conn->query($query);

        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }

        while ($row = $result->fetch_assoc()) {
            $sqlArray[] = $row;
        }

        $this->close();
        return $sqlArray;
    }

    // Führt SQL-Abfrage aus und gibt das Ergebnisobjekt zurück
    public function sql2db(string $query) {
        $result = $this->conn->query($query);

        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }

        $this->close();
        return $result;
    }

    // Schließt die Datenbankverbindung
    public function close() {
        $this->conn->close();
    }
}

// Beispielverwendung
$db = new DB();
// $resultArray = $db->sql2array("SELECT * FROM cml_code");
// $resultArray = $db->sql2db("INSERT into  cml_code (code, code_share) VALUES ('test', 'test')");
var_dump($resultArray);
?>