<?php

class CourseCompletedDAO {
    public function removeAll() {
        $sql = 'TRUNCATE TABLE course_completed;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 
    function validation($userid, $code){
        //Arranged in Alphabetical Order
        $errors = [];
        $sql = "SELECT * FROM student where userid = :userid";
        $sql1 = "SELECT * FROM course where course = :code";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        if (!($stmt->execute())){
            $errors[] = "invalid userid";
        }
        else{
            $resultset = [];
            while ($row = $stmt->fetch()){
                $resultset[] = new Student($row['userid']);
            }
            if ($resultset == []){
                //Check if the user exists
                $errors[] = "invalid userid";
            }
        }

        $stmt1 = $conn->prepare($sql1);
        $stmt1->bindParam(':code', $code, PDO::PARAM_STR);
        if (!($stmt1->execute())){
            $errors[] = "invalid course";
        }
        else{
            $resultset = [];
            while ($row = $stmt1->fetch()){
                $resultset[] = new Course($row['course']);
            }
            if ($resultset == []){
                //Check if the course exists
                $errors[] = "invalid course";
            }
        }
        
        $sql2 = "SELECT * FROM prerequisite WHERE course = :course";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bindParam(':course', $code, PDO::PARAM_STR);
        if ($stmt2->execute()){
            $results = [];
            while($row = $stmt2->fetch(PDO::FETCH_ASSOC)){
                $results[] = new Prerequisite($row['course'], $row['prerequisite']);
            }
            $sql3 = "SELECT * FROM course_completed where userid = :userid";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->bindParam(':userid', $userid, PDO::PARAM_STR);
            
            if ($stmt3->execute()){
                $result_completed = [];
                while($row = $stmt3->fetch(PDO::FETCH_ASSOC)){
                    $result_completed[] = new CourseCompleted($row['userid'], $row['code']);
                }
                $count = 0;
                // prereq code
                $prereq_list = [];
                $completed_list = [];
                //for each prereq obj, get prereq. Use the prereq to check if it's inside course_completed for that spec. user
                foreach($results as $result){
                    $prereq_list[] = $result->getPrerequisite();
                }
                for($i = 0; $i < sizeof($result_completed); $i++){
                    
                    $completed_list[] = $result_completed[$i]->getCode();
                }
                foreach($prereq_list as $prereqs){
                    if (!in_array($prereqs, $completed_list)){
                        return [ "invalid course completed" ];
                    }
                    
                }

            }
            else{
                $errors[] = "invalid course completed";
            }
            
            
        }

        return $errors;
    }

    public function add($courseCompleted) {
        $sql = "INSERT INTO course_completed (userid, code) VALUES (:userid, :code)";
        $is_blank = [];
        if (empty($courseCompleted->getUserid())){
            $is_blank[] = "blank userid";
        }
        if (empty($courseCompleted->getCode())){
            $is_blank[] = "blank code";
        }
        if ($is_blank != []){
            return $is_blank;
        }
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $userid = trim($courseCompleted->userid);        
        $code = trim($courseCompleted->code);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
       

        $isAddOK = False;
        $errors = $this->validation($userid, $code);
        if ($errors != []){
            return $errors;
        }
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }

    public function retrieveCoursesCompleted($userid) {
        //retrieve all courses completed by user
        $sql = "SELECT * FROM course_completed WHERE userid = :userid";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new CourseCompleted($row['userid'], $row['code']);
        }
            
                 
        return $result;
    }
    public  function retrieveAll() {
        //retrieve all courses completed
        $sql = 'SELECT * FROM course_completed ORDER BY code, userid';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new CourseCompleted($row['userid'], $row['code']);
        }
            
                 
        return $result;
    }

};



?>