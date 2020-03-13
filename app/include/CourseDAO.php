<?php

class CourseDAO {
    public function removeAll() {
        $sql = 'TRUNCATE TABLE course;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    function validation($title, $description, $examdate, $start, $end){
        //Arranged in alphabetical order
        $errors = [];

        // if (strlen($examdate) != 8 || (int)(substr($examdate, 4, 2)) > 12 || (int)(substr($examdate, 6)) > 31){
        //     $errors[] = "invalid exam date";
        // }
        $dateFormatCheck = DateTime::createFromFormat('Ymd', $examdate);
        if (!($dateFormatCheck && $dateFormatCheck->format('Ymd') === $examdate)){
            $errors[] = "invalid exam date";
        }

        $start = explode(":", $start);
        //Check if the exam format is inputted correctly
        if (sizeof($start) != 2){
            $errors[] = "invalid exam start";
        }
        else{
            //Check the exam format
            if ((int)$start[0] >= 24 || (int)$start[1] > 60){
                $errors[] = "invalid exam start";
            }
        }
        $end = explode(":", $end);
        //Check exam format and if exam end is after exam start
        if (sizeof($end) != 2 || (int)$end[0] >= 24 || (int)$end[1] > 60 || (int)$end[0] < (int)$start[0] || ((int)$end[0] == (int)$start[0] && (int)$end[1] <= (int)$start[1])){
            $errors[] = "invalid exam end";
        }
            
        if (strlen($title) > 100){
            $errors[] = "invalid title";
        }

        if (strlen($description) > 1000){
            $errors[] = "invalid description";
        }

        return $errors;
    }

    public function add($course) {
        $sql = "INSERT INTO course (course, school, title, description, examdate, examstart, examend) VALUES (:course, :school, :title, :description, :examdate, :examstart, :examend )";
        $is_blank = [];
        if (empty($course->getCourse())){
            $is_blank[] = "blank course";
        }
        if (empty($course->getSchool())){
            $is_blank[] = "blank school";
        }
        if (empty($course->getTitle())){
            $is_blank[] = "blank title";
        }
        if (empty($course->getDescription())){
            $is_blank[] = "blank description";
        }
        if (empty($course->getExamdate())){
            $is_blank[] = "blank exam date";
        }
        if (empty($course->getExamstart())){
            $is_blank[] = "blank exam start";
        }
        if (empty($course->getExamend())){
            $is_blank[] = "blank exam end";
        }
        if ($is_blank != []){
            return $is_blank;
        }
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        
        $course_test = trim($course->course); // course is an object, course_test is a string
        $school = trim($course->school);
        $title = trim($course->title);
        $description = trim($course->description);
        $examdate = trim($course->examdate);
        $examstart = trim($course->examstart);
        $examend = trim($course->examend);

        $errors = $this->validation($title, $description, $examdate, $examstart, $examend);

        if ($errors != []){
            return $errors;
        }
        // $errors = ["testing!"];
        // return $errors;

        $stmt->bindParam(':course', $course_test, PDO::PARAM_STR);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':examdate', $examdate, PDO::PARAM_STR);
        $stmt->bindParam(':examstart', $examstart, PDO::PARAM_STR);
        $stmt->bindParam(':examend', $examend, PDO::PARAM_STR);

        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }

    public function getCourse($course){
        $conn = new ConnectionManager();
        $pdo = $conn->getConnection();
        $sql = "SELECT * from course where course =:course";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":course",$course,PDO::PARAM_STR)
        ;
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $row = $stmt->fetch();
        $result = new Course($row["course"],$row["school"],$row["title"],$row["description"],$row["examdate"],$row["examstart"],$row["examend"]);
        $stmt->closeCursor();
        $pdo = null;
        return $result;
        }

    public function courseExist($course){
        $conn = new ConnectionManager();
        $pdo = $conn->getConnection();
        $sql = "SELECT * from course where course =:course";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":course",$course,PDO::PARAM_STR)
        ;
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = null;
        if ($row = $stmt->fetch()){
            $result = new Course($row["course"],$row["school"],$row["title"],$row["description"],$row["examdate"],$row["examstart"],$row["examend"]);
        }
        $stmt->closeCursor();
        $pdo = null;
        return $result;
        }
    public  function retrieveAll() {
        $sql = 'SELECT * from course ORDER BY course';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Course($row["course"],$row["school"],$row["title"],$row["description"],$row["examdate"],$row["examstart"],$row["examend"]);
        }
        return $result;
    }

};



?>