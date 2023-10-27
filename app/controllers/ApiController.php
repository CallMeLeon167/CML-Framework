<?php 
namespace CML\Controllers;

class ApiController{
    public function getRepoData($params) {
        $url = $params['url'];
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        $data = json_decode($response, true);
    
        return is_array($data) ? $data : false;
    }
    
}
?>