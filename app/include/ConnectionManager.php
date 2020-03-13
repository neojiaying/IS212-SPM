<?php

class ConnectionManager {
   
    public function getConnection() {
        
        $host = "localhost";
        $username = "root";
        $password = "";  #fMn7vn5F5QVY
        $dbname = "project_g4t1";
        $port = 3306;    

        $url  = "mysql:host={$host};dbname={$dbname};port={$port}";
        
        $conn = new PDO($url, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        return $conn;  
        
    }
    
}
