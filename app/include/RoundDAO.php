<?php

class RoundDAO {

    public function startRound(){
        $sql = "UPDATE round SET round = round + 1, status = 'active'";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute();
        $isAddOk = False;
        if ($result){
            $isAddOk = true;
        }
        return $isAddOk;
        
    }

    public function startRoundOne(){
        $sql = "UPDATE round SET round = 1, status = 'active'";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute();
        $isAddOk = False;
        if ($result){
            $isAddOk = true;
        }
        return $isAddOk;
        
    }

    public function endRound(){
        $sql = "UPDATE round SET status = 'inactive'";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute();
        $isAddOk = False;
        if ($result){
            $isAddOk = true;
        }
        return $isAddOk;

    }
    public function defaultRound(){
        $sql = "UPDATE round SET round = 0, status = 'inactive'";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        
        $isAddOk = False;
        if ($stmt->execute()){
            $isAddOk = True;
        }
        return $isAddOk;

    }

    public function getDetails(){
        $sql = "SELECT * FROM round";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = [];
        while($row = $stmt->fetch()){
            $result[] = new Round($row['round'], $row['status']);
        }
        return $result;
    }

}