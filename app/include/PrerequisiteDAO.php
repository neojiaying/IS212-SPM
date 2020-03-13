<?php

class PrerequisiteDAO{
    public function removeAll(){
        $sql = 'TRUNCATE TABLE prerequisite;';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->execute();
        $count = $stmt->rowCount();
    }

    public function validation($course, $prereq){
        //To store the relevant errors
        $errors = []; 
        $sql = "SELECT * FROM course where course = :course";
        $sql1 = "SELECT * FROM course where course = :prereq";

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        if (!($stmt->execute())) {
            $errors[] = "invalid course";
        } 
        else {
            $resultset = [];
            while ($row = $stmt->fetch()) {
                $resultset[] = new Course($row['course']);
            }
            if ($resultset == []) { 
                //The Course checked for a prerequisite isn't in the course database
                $errors[] = "invalid course";
            }
        }

        $stmt1 = $conn->prepare($sql1);
        $stmt1->bindParam(':prereq', $prereq, PDO::PARAM_STR);
        $prereq_check = false;
        if (!($stmt1->execute())) {
            $errors[] = "invalid prerequisite";
        } 
        else {
            $resultset = [];
            while ($row = $stmt1->fetch()) {
                $resultset[] = new Course($row['course']); 
            }
            if ($resultset == []) {
                //The prerequisite checked isn't in the course database
                $errors[] = "invalid prerequisite"; 
            }
        }

        return $errors;
    }

    public function add($prerequisite){
        $sql = "INSERT INTO prerequisite (course, prerequisite) VALUES (:course, :prerequisite)";
        //checking if any input is blank. If it is, return the rows that were blank.
        $is_blank = []; 
        if (empty($prerequisite->getCourse())) {
            $is_blank[] = "blank course";
        }
        if (empty($prerequisite->getPrerequisite())) {
            $is_blank[] = "blank prerequisite";
        }
        if ($is_blank != []) {
            return $is_blank;
        }
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $course = trim($prerequisite->course);
        $prereq = trim($prerequisite->prerequisite);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':prerequisite', $prereq, PDO::PARAM_STR);

        $isAddOK = false;
        $errors = $this->validation($course, $prereq);
        if ($errors != []) {
            return $errors;
        }
        if ($stmt->execute()) {
            $isAddOK = true;
        }

        return $isAddOK;
    }
    public  function retrieveAll() {
        //Retrieve all data in prerequisite
        $sql = 'SELECT * FROM prerequisite ORDER BY course, prerequisite';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Prerequisite($row['course'], $row['prerequisite']);
        }
            
                 
        return $result;
    }
};
