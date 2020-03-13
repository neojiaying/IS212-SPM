<?php
#need to edit
class SectionDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM section ORDER BY course, section';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Section($row['course'], $row['section'],$row['day'],$row['start'],$row['end'],$row['instructor'],$row['venue'],$row['size']);
        }
            
                 
        return $result;
    }

    function validation($course, $section, $day, $start, $end, $instructor, $venue, $size){
        $errors = [];
        $sql = "SELECT * FROM course where course = :course";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        if (!($stmt->execute())){
            $errors[] = "invalid course";
        }
        else{
            $resultset = [];
            while ($row = $stmt->fetch()){
                $resultset[] = new Course($row['course']);
            }
            if ($resultset == []){
                $errors[] = "invalid course";
            }
            else{
                if ( substr($section, 0, 1) != 'S' || strlen($section) > 3 || $section == 'S0'){
                    $errors[] = "invalid section";
                }
            }
        }

        

        if ($day > 7 || $day < 1){
            $errors[] = "invalid day";
        }
        //Explode to compare numeric values
        $start = explode(":", $start);
        if (sizeof($start) != 2 ){
            $errors[] = "invalid start";
        }
        else{
            if ((int)$start[0] >= 24 || (int)$start[1] > 60){
                $errors[] = "invalid start";
            }
        }
        //Explode the compare numeric values
        $end = explode(":", $end);
        if (sizeof($end) != 2 || (int)$end[0] >= 24 || (int)$end[1] > 60 || (int)$end[0] < (int)$start[0] || ((int)$end[0] == (int)$start[0] && (int)$end[1] <= (int)$start[1])){
            $errors[] = "invalid end";
        }

        if (strlen($instructor) > 100){
            $errors[] = "invalid instructor";
        }

        if (strlen($venue) > 100){
            $errors[] = "invalid venue";
        }

        if ($size <= 0){
            $errors[] = "invalid size";
        }
        
        return $errors;
    }

    public function add($section) {
        $sql = "INSERT INTO section (course, section, day, start, end, instructor, venue, size) VALUES (:course, :section, :day, :start, :end, :instructor, :venue, :size)";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $is_blank = [];
        if (empty($section->getCourse())){
            $is_blank[] = "blank course";
        }
        if (empty($section->getSection())){
            $is_blank[] = "blank section";
        }
        if (empty($section->getDay())){
            $is_blank[] = "blank day";
        }
        if (empty($section->getStart())){
            $is_blank[] = "blank start";
        }
        if (empty($section->getEnd())){
            $is_blank[] = "blank end";
        }
        if (empty($section->getInstructor())){
            $is_blank[] = "blank instructor";
        }
        if (empty($section->getVenue())){
            $is_blank[] = "blank venue";
        }
        if ($section->getSize() == ''){
            $is_blank[] = "blank size";
        }
        if ($is_blank != []){
            return $is_blank;
        }
        $course = trim($section->course);
        $section_test = trim($section->section); //original section is an object - section_test is a string
        // return [$section_test];
        $day = $section->day;
        $start = trim($section->start);
        $end = trim($section->end);
        $instructor = trim($section->instructor);
        $venue = trim($section->venue);
        $size = trim($section->size);
        $errors = $this->validation($course, $section_test, $day, $start, $end, $instructor, $venue, $size);

        if ($errors != []){
            return $errors;
        }

        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section_test, PDO::PARAM_STR);
        $stmt->bindParam(':day', $day, PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, PDO::PARAM_STR);
        $stmt->bindParam(':end', $end, PDO::PARAM_STR);
        $stmt->bindParam(':instructor', $instructor, PDO::PARAM_STR);
        $stmt->bindParam(':venue', $venue, PDO::PARAM_STR);
        $stmt->bindParam(':size', $size, PDO::PARAM_INT);

        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }
    
    public function removeAll() {
        $sql = 'TRUNCATE TABLE section;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }    
    public function updateSectionSize($numsuccessbids, $course, $section, $size) {
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $sql = "UPDATE section SET size = :size - $numsuccessbids WHERE course = :course and section = :section";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':size', $size, PDO::PARAM_INT);
        $stmt->execute();
        
    }    




    public function getSection($course){
        $result = [];
        $conn = new ConnectionManager();
        $pdo = $conn->getConnection();
        $sql = "SELECT section from section where course =:course";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":course",$course,PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while($row = $stmt->fetch()){
            $result[] = $row["section"];
        }
        
        $stmt->closeCursor();
        $pdo = null;
        return $result;
    }

    public function getVacancy($course,$section){
        $result = [];
        $conn = new ConnectionManager();
        $pdo = $conn->getConnection();
        $sql = "SELECT size from section where course =:course AND section = :section";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":course",$course,PDO::PARAM_STR);
        $stmt->bindParam(":section",$section,PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while($row = $stmt->fetch()){
            $result[] = $row["size"];
        }
        
        $stmt->closeCursor();
        $pdo = null;
        return $result;
    }
}