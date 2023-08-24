<?php 
namespace Classes;
use Classes\DB;

class Login extends DB{

    public function __construct(){
        session_start();
        parent::connect();
    }

    public function getUserData(){
        $id = $_SESSION['id'];
        if(!empty($id)){
            $user_data = parent::sql2array_file("userData.sql", [$id]);
            return $user_data[0];
        } else {
            return false; //user ist nicht angemeldet
        }
    }

    public function login(string $username, string $password, string $redirect = "./"){
        if($_SERVER['REQUEST_METHOD'] != "POST"){
            $user_data = parent::sql2array("SELECT * FROM k200156_devLinks.user WHERE username = ?", [$username]);
            
            if($user_data){
                $user_data = $user_data[0];
                $dbPassword = $user_data['password'];

                $savedSalt = $user_data['password_salt']; // random salt from DB
                $hashedPassword = hash('sha256', $password.$savedSalt); // hashed password with sha256 algorithm

                if ($hashedPassword === $dbPassword) {
                    // Einloggen erfolgreich
                    echo "your in";
                    $_SESSION['id'] = $user_data['id'];
                } else {
                    // falsches passwort
                    echo "your not in";
                }
            } else {
                return false; //user existiert nicht
            }
        }
    }

    public function register(string $username, string $password, string $redirect = "./"){
        if($_SERVER['REQUEST_METHOD'] != "POST"){

            $salt = $this->generateSalt(); // random Salt
            $hashedPassword = hash('sha256', $password.$salt); // hashed password with sha256 algorithm

            $usernameCheck = parent::sql2array("SELECT * FROM k200156_devLinks.user WHERE username = ?", [$username]);
            if($usernameCheck){
                return false; // username is taken
            } else {
                parent::sql2db("INSERT into k200156_devLinks.user (`userid`, `username`, `password`, `password_salt`, `created`)
                VALUES (?, ?, ?, ?, now())", [$this->generateUserID(), $username, $hashedPassword, $salt]);
                return true;
            }
        }
    }

    public function logout(string $redirect = "./"){
        if(isset($_SESSION['id'])){
            unset($_SESSION['id']);
        }

        header("Location: $redirect");
        die;
    }

    private function generateUserID() {
        $min = 100000;
        $max = 999999;
        $userid = mt_rand($min, $max);

        $query = "SELECT * FROM k200156_devLinks.user WHERE `userid` = ?";
        $check = parent::sql2array($query, [$userid]);
        if($check){
            while($check){
                $userid = mt_rand($min, $max);
                $check = parent::sql2array($query, [$userid]);
            }
        }
        return $userid;
    }

    private static function generateSalt() {
        return bin2hex(random_bytes(16));
    }
}

?>