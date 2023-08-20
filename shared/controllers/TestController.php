<?php 

namespace Controllers;

use Classes\DB;

class TestController extends DB{
    public function getTest($params) {
        // Hier kannst du die Logik für die Anzeige der Nachricht mit der gegebenen ID implementieren

        // Beispiel:
        $arrID = ['id' => $params['id']];
        var_dump($arrID);

        $news = DB::sql2array("SELECT * FROM news");
        var_dump($news);
    }
}
?>