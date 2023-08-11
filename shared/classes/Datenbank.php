<?php 
class DB {
    private $conn;

    public function __construct() {
        $this->loadEnv();
        $this->connect();
    }

    private function loadEnv() {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ ."/../config");
        $dotenv->load();
    }

    private function connect() {
        $host = $_ENV['DB_HOST'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $dbname = $_ENV['DB_NAME'];

        if($_SERVER['HTTP_HOST'] != 'localhost') {
            mysqli_report(MYSQLI_REPORT_OFF);
        }

        $this->conn = new mysqli($host, $user, $pass, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function sql2array($query) {
        $sqlArray = array();
        $result = $this->conn->query($query);

        while ($row = $result->fetch_assoc()) {
            $sqlArray[] = $row;
        }

        $this->close();
        return $sqlArray;
    }

    public function sql2db($query) {
        $result = $this->conn->query($query);
        $this->close();
        return $result;
    }

    public function close() {
        $this->conn->close();
    }
}

// Beispielverwendung
$db = new DB();
$resultArray = $db->sql2array("SELECT * FROM cml_code");
var_dump($resultArray);
?>