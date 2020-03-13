<?php

class BidDAO { 
    public function removeAll() {
        $sql = 'TRUNCATE TABLE bid;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    function validation($userid, $amount, $code, $section, $round){
        $errors = []; //Check for errors
        $sql = "SELECT * FROM student where userid = :userid";
        $sql1 = "SELECT * FROM course where course = :code";
        $sql2 = "SELECT * FROM section where course = :code and section = :section";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);

        if (!($stmt->execute())){
            $errors[] = "invalid userid";
        }
        else{
            $studentset = [];
            while ($row = $stmt->fetch()){
                $studentset[] = new Student($row['userid'], $row['password'], $row['name'], $row['school'], $row['edollar']);
            }
            if ($studentset == []){ //Check if the userid exists in the student databsae
                $errors[] = "invalid userid";
            }
            else{
                //There should only be one student with the particular userid
                $user_school = $studentset[0]->getSchool();
                $user_edollar = $studentset[0]->getEdollar();
            }
        }

        $edollar_array = explode(".", $amount);
        if ((int)$amount < 0 || sizeof($edollar_array) > 2 || (sizeof($edollar_array) == 2 && strlen($edollar_array[1])  > 2)){ 
            //check if the amount is less than 0, or if the user did not type a number
            //check if there is decimal place. If there is, check if less or equal to 2dp
            $errors[] = "invalid amount";
        }
        elseif ($amount < 10){
            $errors[] = "invalid amount";
        }

        $stmt1 = $conn->prepare($sql1);
        $stmt1->bindParam(':code', $code, PDO::PARAM_STR);
        if (!($stmt1->execute())){
            $errors[] = "invalid course";
        }
        else{
            $courseset = [];
            while ($row = $stmt1->fetch()){
                $courseset[] = new Course($row['course'], $row['school'],$row['title'], $row['description'],$row['examdate'],$row['examstart'],$row['examend']);
            }
            if ($courseset == []){
                //Check if the course bidded is available
                $errors[] = "invalid course";
            }
            else{
                //If course is valid
                //There should only be one course that matches
                //$courseset[0] as result is indexed array - these results are later used for exam validation
                $course_school = $courseset[0]->getSchool();
                $course_examdate = $courseset[0]->getExamdate();
                $course_examstart = $courseset[0]->getExamstart();
                $course_examend = $courseset[0]->getExamend();
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bindParam(':code', $code, PDO::PARAM_STR);
                $stmt2->bindParam(':section', $section, PDO::PARAM_STR);
                if (!($stmt2->execute())){
                    $errors[] = "invalid section";
                }
                else{
                    $sectionset = [];
                    while ($row = $stmt2->fetch()){
                        $sectionset[] = new Section($row['course'], $row['section'],$row['day'], $row['start'],$row['end'],$row['instructor'],$row['venue'],$row['size']);
                    }
                    if ($sectionset == []){
                        //Check if the section exists
                        $errors[] = "invalid section";
                    }
                    else{
                        //There should only be one section that matches the section and corresponding course
                        //these results are later used for timing validation
                        $section_day = $sectionset[0]->getDay();
                        $section_start = $sectionset[0]->getStart();
                        $section_end = $sectionset[0]->getEnd();
                        $section_size = $sectionset[0]->getSize();
                    }
                }
            }
        }
        

        
        if ($errors != []){ //check for all invalid fields before carrying out bid specific validations
            return $errors;
        }
        
        //Check if section size is biddable (above 0)
        if ($round == 2 && $section_size == 0){
            $errors[] = "no vacancy";
        }

        //In round 1, if the use does not bid for his/her own school course
        if ($round <= 1 && $user_school != $course_school){
            $errors[] = "not own school course";
        }

        $previous_bids = $this->getPreviousBids($userid);
        $limit = 5; //max courses that user can take - hardcoded to 5 based on wiki specifications
        $previous_course_times = [];
        $previous_section_times = [];

        foreach($previous_bids as $previous_bid){
            //getting all of the course and section details per bid
            $previous_course_times[] = $this->getCourseDetails($previous_bid->getCode());
            $previous_section_times[] = $this->getSectionDetails($previous_bid->getCode(), $previous_bid->getSection());
        }
        $is_update = false; 
        //is update refers to if the user is bidding for a mod they have already bidded for
        $updated_amount = $amount;
        foreach($previous_bids as $previous_bid){
            //if course is same and round is same, means it is an update
            if ($previous_bid->getCode() == $code && $previous_bid->getRound() == $_SESSION['round']){
                //if the user has bidded for the previous course
                $holding_bid = $previous_bid->getAmount();
                $updated_amount -= $previous_bid->getAmount();
                $is_update = true;
                $limit += 1;//to indicate that there should not be exceeding bid error
            }
            elseif ($previous_bid->getCode() == $code && $previous_bid->getRound() != $_SESSION['round']){
                $comparison = $previous_bid;
            }
        }
        foreach($previous_section_times as $previous_section_time){
            //per section that the student has bidded for, get all of the timings
            $previous_section_time = $previous_section_time[0];
            if ($previous_section_time->getCourse() != $code && $section_day == $previous_section_time->getDay()){ 
                //If it's not the same course and the start/end time is in between a course
                //Explode array to compare based on integer values (for hour (0) and minute (1))
                $biddedexamstart = explode(":",$section_end);
                $biddedexamend = explode(":", $section_start);
                $previousexamstart = explode(":", $previous_section_time->getStart());
                $previousexamend = explode(":", $previous_section_time->getEnd());
                // check if section start is after section end for bidded or if section end is before section start for bidded
                if (($biddedexamstart[0] > $previousexamend[0]) || ($biddedexamstart[0] == $previousexamend[0] && $biddedexamstart[1] > $previousexamend[1]) || ($previousexamstart[0] > $biddedexamend[0]) || ($previousexamstart[0] == $biddedexamend[0] && $previousexamstart[1] > $biddedexamend[1])) { //check if a previous exams clashes
                    continue;
                }
                else{
                    $errors[] = "class timetable clash";
                }

            }
            elseif ($previous_section_time->getCourse() == $code && isset($comparison)){
                if ($comparison->getRound() != $_SESSION['round']){
                    //Explode array to compare based on integer values (for hour (0) and minute (1))
                    $biddedexamstart = explode(":",$section_end);
                    $biddedexamend = explode(":", $section_start);
                    $previousexamstart = explode(":", $previous_section_time->getStart());
                    $previousexamend = explode(":", $previous_section_time->getEnd());
                    if (($biddedexamstart[0] > $previousexamend[0]) || ($biddedexamstart[0] == $previousexamend[0] && $biddedexamstart[1] > $previousexamend[1]) || ($previousexamstart[0] > $biddedexamend[0]) || ($previousexamstart[0] == $biddedexamend[0] && $previousexamstart[1] > $biddedexamend[1])) { //check if a previous exams clashes
                        continue;
                    }
                    else{
                        $errors[] = "class timetable clash";
                    }
                }
            }
            // elseif ($previous_section_time->getCourse() == $code && $previous_section_time->getSection() == $section){
            //     $errors[] = "class timetable clash";
            // }
        }

        foreach($previous_bids as $previous_bid){
            //getting all of the course and section details per bid
            if ($previous_bid->getCode() == $code && $_SESSION['round'] == 2 && $previous_bid->getRound() == 1){
                $errors[] = "course enrolled";
            }
        }

        foreach($previous_course_times as $previous_course_time){
            //per courses that the student has bid for, get all of the timings
            $previous_course_time = $previous_course_time[0];
            if ($previous_course_time->getCourse() != $code && $course_examdate == $previous_course_time->getExamdate()){ 
                //If it's not the same course and the start/end time is in between an exam
                //Explode array to compare based on integer values (for hour (0) and minute (1))
                $biddedexamstart = explode(":",$course_examstart);
                $biddedexamend = explode(":", $course_examend);
                $previousexamstart = explode(":", $previous_course_time->getExamstart());
                $previousexamend = explode(":", $previous_course_time->getExamend());
                // check if exam start is after exam end for bidded or if exam end is before exam start for bidded
                if (((int)$biddedexamstart[0] > (int)$previousexamend[0]) || ((int)$biddedexamstart[0] == (int)$previousexamend[0] && (int)$biddedexamstart[1] >= (int)$previousexamend[1]) || ((int)$previousexamstart[0] > (int)$biddedexamend[0]) || ((int)$previousexamstart[0] == (int)$biddedexamend[0] && (int)$previousexamstart[1] >= (int)$biddedexamend[1])) { //check if a previous exams clashes
                    continue;
                }
                else{
                    $errors[] = "exam timetable clash";
                }
            }
            elseif ($previous_course_time->getCourse() == $code && isset($comparison)){
                if ($comparison->getRound() != $_SESSION['round']){
                    //Explode array to compare based on integer values (for hour (0) and minute (1))
                    $biddedexamstart = explode(":",$course_examstart);
                    $biddedexamend = explode(":", $course_examend);
                    $previousexamstart = explode(":", $previous_course_time->getExamstart());
                    $previousexamend = explode(":", $previous_course_time->getExamend());
                    // check if exam start is after exam end for bidded or if exam end is before exam start for bidded
                    if (((int)$biddedexamstart[0] > (int)$previousexamend[0]) || ((int)$biddedexamstart[0] == (int)$previousexamend[0] && (int)$biddedexamstart[1] >= (int)$previousexamend[1]) || ((int)$previousexamstart[0] > (int)$biddedexamend[0]) || ((int)$previousexamstart[0] == (int)$biddedexamend[0] && (int)$previousexamstart[1] >= (int)$biddedexamend[1])) { //check if a previous exams clashes
                        continue;
                    }
                    else{
                        $errors[] = "exam timetable clash";
                    }
                }
            }
        }
        //check if prerequisites are satisfied
        if (!($this->clearPrerequisite($userid, $code))){
            $errors[] = "incomplete prerequisites";
        }

        $sql4 = "SELECT * FROM course_completed WHERE userid = :userid";
        $stmt4 = $conn->prepare($sql4);
        $stmt4->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt4->execute();
        $courses_completed = [];
        while ($row = $stmt4->fetch()){
            $courses_completed[] = new CourseCompleted($row['userid'], $row['code']);
        }
        foreach($courses_completed as $course_completed){
            //Checking if user's bidded course is already completed for the user
            if ($course_completed->getCode() == $code){
                $errors[] = "course completed";
                break;
            }
        }
        
        //Check the number of previous bids against the limit. If there is already 5 (and not an update), error out
        if (sizeof($previous_bids) >= $limit){
            $errors[] = "section limit reached";
        }
        
        

        if ($amount < 10 || $updated_amount > $user_edollar){
            //If the bid amount is too low, or if the updated amount would be less than 0
            $errors[] = "not enough e-dollar";
        }
        if ($errors == [] && $is_update){
            //Deleting existing bid to make space for updated bid
            $sql5 = "DELETE from bid WHERE code = :code and userid = :userid";
            $stmt5 = $conn->prepare($sql5);
            $stmt5->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt5->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt5->execute();
            $sql6 = "UPDATE student SET edollar = edollar + $holding_bid where userid=:userid";
            $stmt6 = $conn->prepare($sql6);
            $stmt6->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt6->execute();

        }
        return $errors;

    }

    public function add($bid, $round) {
        $sql = "INSERT INTO bid (userid, amount, code, section, status, round) VALUES (:userid, :amount, :code, :section, 'pending', :round)";
        //Check for blank data
        $is_blank = [];
        if (empty($bid->getUserid())){
            $is_blank[] = "blank userid";
        }
        if (empty($bid->getAmount())){
            $is_blank[] = "blank amount";
        }
        if (empty($bid->getCode())){
            $is_blank[] = "blank code";
        }
        if (empty($bid->getSection())){
            $is_blank[] = "blank section";
        }
        if ($is_blank != []){
            return $is_blank;
        }
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        //Remove whitespace
        $userid = trim($bid->userid);
        $amount = trim($bid->amount);
        $code = trim($bid->code);
        $section = trim($bid->section);

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);

        $isAddOK = False;
        $errors = $this->validation($userid, $amount, $code, $section, $round);
        $error=[];
        foreach($errors as $one_errors){
            if($one_errors == "invalid amount" && $bid ->getAmount() < 10){
                $error[] = $one_errors;
                // $error[] = "bid too low";
            }
            else{
                $error[] = $one_errors;
            }
        }
      
        
        if ($error != []){
            return $error;
        }
        if ($stmt->execute()) {
            $isAddOK = True;
            $sql1 = "UPDATE student SET edollar = edollar - :amount WHERE userid = :userid";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt1->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt1->execute();
        }
        return $isAddOK;
    }
    public function addforround($bid){
        //Adding a bid for a specific round
        $sql = "INSERT INTO bid (userid, amount, code, section, status, round) VALUES (:userid, :amount, :code, :section, 'pending', :round)";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $userid = trim($bid->userid);
        $amount = trim($bid->amount);
        $code = trim($bid->code);
        $section = trim($bid->section);
        $round = trim($bid->round);
        $isAddOk = False;

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);

        if ($stmt->execute()){
            $isAddOk = True;
        }
        return $isAddOk;

    }

    public function remove($userid, $code) {
        //Remove a bid - only applies for round 1
        $sql = "DELETE FROM bid where userid = :userid and code = :code";
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);

        $stmt->execute();
    }
    public function retrieveSectionsByCourse($course) {
        //Retrieve all sections for 1 course
        $sql = "SELECT * FROM section WHERE course = :course";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Section($row['course'],$row['section'],$row['day'],$row['start'],$row['end'],$row['instructor'],$row['venue'],$row['size']);
        }
            
                 
        return $result;
    }

    public function retrieveBidsByUser($userid) {
        //Retrieve all bids by user in associative array
        $sql = "SELECT * FROM bid WHERE userid = :userid";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Section($row['course'],$row['section'],$row['day'],$row['start'],$row['end'],$row['instructor'],$row['venue'],$row['size']);
        }
            
                 
        return $result;
    }
  

    public function getPreviousBids($userid) {
        //Retrieve all bids by user
        $sql = "SELECT * from bid WHERE userid = :userid"; // what has this user bidded for

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
    
        $stmt->bindParam(':userid',$userid, PDO::PARAM_STR);
        $stmt->execute();
        $result = array();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'], $row['status'], $row['round']);
        }
        return $result;
    }



    public function getCourseDetails($one_bid_course) {
        $sql = "SELECT * from course WHERE course = :course";  // get course examdate,start,end for previous bids

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
    
        $stmt->bindParam(':course',$one_bid_course, PDO::PARAM_STR);
        $stmt->execute();

        $result2 = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result2[] = new Course($row['course'], $row['school'],$row['title'], $row['description'],$row['examdate'],$row['examstart'],$row['examend']);
        }

        return $result2;
    }



    public function getSectionDetails($one_bid_course,$one_bid_section) {
    $sql = "SELECT * from section WHERE course = :course AND section=:section"; //get section day,start,end for previous bids

    $connMgr = new ConnectionManager();      
    $conn = $connMgr->getConnection();
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':course',$one_bid_course, PDO::PARAM_STR);
    $stmt->bindParam(':section',$one_bid_section, PDO::PARAM_STR);
    $stmt->execute();

    $result3 = array();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result3[] = new Section($row['course'], $row['section'],$row['day'], $row['start'],$row['end'],$row['instructor'],$row['venue'],$row['size']);
        }

    return $result3;
    }

    public function updateCredits($balance,$userid){
        //update student edollar based on updated bid
        $sql = "UPDATE student SET edollar = :edollar WHERE userid = :userid";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
    
        $stmt->bindParam(':edollar', $balance, PDO::PARAM_STR);
        $stmt->bindParam(':userid',$userid, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updateStatus($status,$userid,$code,$section,$round){
        //Update the status of bid based on round processing
        $sql = "UPDATE bid SET status = :status WHERE userid = :userid AND code = :code AND section = :section AND round = :round";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
    
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':userid',$userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);
        $stmt->execute();

        return $status;
    }

    public function clearPrerequisite($userid,$course){
        //Check if user has cleared prerequisite
        $sql = "SELECT * from course_completed WHERE userid = :userid"; 
        $sql1 = "SELECT * FROM prerequisite where course = :course";

        

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
       
        $stmt->bindParam(':userid',$userid, PDO::PARAM_STR);
        $stmt->execute();
        $result = array();
    
    
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Coursecompleted($row['userid'], $row['code']);
        } //see if the user has completed any courses

        
        $stmt1 = $conn->prepare($sql1);
        
        $stmt1->bindParam(':course',$course, PDO::PARAM_STR);
        $stmt1->execute();
        $result2=array();

        while($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
            $result2[] = new Prerequisite($row['course'], $row['prerequisite']);
        } // see whether that course has prerequisite
        
        if($result2==[]){
            return True;
        }
        if($result==[] && isset($result2)){
            return False;
        }
        
        // prereq code
        $prereq_list = [];
        $completed_list = [];
        //for each prereq obj, get prereq. Use the prereq to check if it's inside course_completed for that spec. user
        foreach($result2 as $prereq){
            //prereq code == 
            $prereq_list[] = $prereq->getPrerequisite();
        }
        for($i = 0; $i < sizeof($result); $i++){
            
            $completed_list[] = $result[$i]->getCode();
        }

        foreach($prereq_list as $prereqs){
            if (!in_array($prereqs, $completed_list)){
                return False;
            }
            
        }
        if (count($prereq_list) != count($completed_list)){
            return False;
        }
        return True;

        
        

    }

    public function retrieveSpecificBid($code,$section) {
        //Retrieve all bids for a course and its section
        $sql = "SELECT * FROM bid WHERE code = :code and section=:section ORDER BY amount DESC";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        }
            
                 
        return $result;
    }

    public function retrieveSpecificBidRoundTwo($code,$section) {
        //Retrieve all bids for a course and its section, but for round 2
        $sql = "SELECT * FROM bid WHERE code = :code and section=:section and round = 2 ORDER BY amount DESC";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'], $row['status'], $row['round']);
        }
            
                 
        return $result;
    }

    public function retrieveSpecificBidDumpBid($code,$section) {
        //retroeve specific bid based on course and section for bid
        $sql = "SELECT * FROM bid WHERE code = :code and section=:section ORDER BY userid ASC";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        }
            
                 
        return $result;
    }
    public  function retrieveforDump() {
        //retrieve dump info required
        $sql = 'SELECT userid, amount, code, section FROM bid ORDER BY code, section, amount DESC, userid';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }
            
                 
        return $result;
    }

    public  function retrieveAll() {
        //retrieve all bids
        $sql = 'SELECT * FROM bid ORDER BY code, section, amount DESC, userid';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        
        }
            
                 
        return $result;
    }

    public  function retrieveAllForDump() {
        //retrieve all bids specifically for dump
        $sql = 'SELECT * FROM bid ORDER BY userid ASC';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        
        }
            
                 
        return $result;
    }
    public function removeforround($userid, $code, $round) {
        //remove a bid for a specific round
        $sql = "DELETE FROM bid where userid = :userid and code = :code and round = :round";
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);

        $stmt->execute();
    }
    public function retrieveSpecificBidStatus1($code,$section) {
        //Retrieve specific bid status for round 1
        $sql = "SELECT * FROM bid WHERE code = :code and section=:section and round = 1 ORDER BY amount DESC";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        }
            
                 
        return $result;
    }

    public function retrieveBidByStatus1($code,$section,$status) {
        //retrieve bids by status for round 1
        $sql = "SELECT * FROM bid WHERE code = :code and section=:section and status= :status and round=1 ORDER BY amount DESC";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        }
            
                 
        return $result;
    }

    public function retrieveSpecificBidStatus2($code,$section) {
        //Retrieve specific bid status for round 2
        $sql = "SELECT * FROM bid WHERE code = :code and section=:section and round = 2 ORDER BY amount DESC";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        }
            
                 
        return $result;
    }

    public function retrieveAllSuccessBid($code,$section) {
        //Retrieve all successful bids
        $sql = "SELECT * FROM bid WHERE code = :code and section=:section and status = 'Success' ORDER BY amount DESC";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'],$row['status'], $row['round']);
        }
            
                 
        return $result;
    }
      
};

?>