<?php
require_once 'common.php';
class LastBidDAO{
    public function add($userid,$amount,$code,$section){
        $sql = "INSERT INTO lastbid (userid, amount, code, section, status, round) VALUES (:userid, :amount, :code, :section, 'lastbid', '2')";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
    
        $isAddOk = False;

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
       
      

        if ($stmt->execute()){
            $isAddOk = True;
        }
        return $isAddOk;

    }

    public function replace($userid,$amount,$code,$section){
        $sql1 = "UPDATE lastbid SET userid = :userid WHERE code = :code AND section = :section" ;
        $sql2 = "UPDATE lastbid SET amount = :amount WHERE code = :code AND section = :section" ;
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt1 = $conn->prepare($sql1);
        $stmt2 = $conn->prepare($sql2);
    
        $isAddOk = False;

        $stmt1->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt2->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt1->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt1->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt2->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt2->bindParam(':section', $section, PDO::PARAM_STR);

        $stmt1 -> execute();
        $stmt2 -> execute();
       

    }

    public  function retrieveAll() {
        $sql = 'SELECT * FROM lastbid';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new LastBid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        
        }
        return $result;
    }

    public function removeAll() {
        $sql = 'TRUNCATE TABLE lastbid;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
    } 
    public  function retrieveforDump() {
        $sql = 'SELECT userid, amount, code, section FROM lastbid ORDER BY code, section, amount DESC, userid';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new LastBid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }
            
                 
        return $result;
    }

}


?>