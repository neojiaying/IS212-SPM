<?php

class AdminDAO {
    //Retrieve admin details to verify admin login
    public function retrieve($username) {
        $sql = 'select * from admin where username=:username';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Admin($row['username'], $row['password']);
        }
    }

    public function removeAll() {
        $sql = 'TRUNCATE TABLE admin;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }    


}