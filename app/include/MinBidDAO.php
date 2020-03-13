<?php
require_once 'common.php';
class MinBidDAO{
    public function removeAll() {
        $sql = 'TRUNCATE TABLE minbid;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    public function add($course, $section){
        //populate minbid based on course and section. default 10 as this is bootstrap (round 1 active default)
        $sql = 'INSERT INTO minbid (course, section, minbid) VALUES (:course, :section, 10)';
        $course = trim($course);
        $section = trim($section);
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->execute();

    }

    public function retrieve($course, $section){
        //get the minimum bid for a particular course/section combination
        $sql = 'SELECT * FROM minbid WHERE course = :course and section = :section';
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()){
            $result[] = new MinBid($row['course'], $row['section'], $row['minbid']);
        }   
        return $result;
    }
    public function update($course,$section,$minbid){
        //update minimum bid for a particular course/section combination
        $sql = "UPDATE minbid SET minbid = :minbid WHERE course = :course AND section = :section";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':minbid', $minbid, PDO::PARAM_STR);
        $stmt->execute();
    }

}