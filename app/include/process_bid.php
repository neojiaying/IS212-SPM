<?php
require_once 'common.php';

$course = strtoupper($_POST['course']);
$section = strtoupper($_POST['section']);
$amount = $_POST['amount']; //the amt amy ng wants to bid for the course
$school = $_SESSION['school']; //amy ng school is sis
// $balance = $_SESSION['edollar']; // amy ng balance
$userid = $_SESSION['userid'];

$dao_student = new StudentDAO();
$student = $dao_student->retrieve($userid);

$name = $student->getName();
$balance = $student->getEdollar();

if (trim($course) == '' || trim($section) == '' || trim($amount) == '') { 
    //check if form is filled
    $error[] = "Please fill up the whole form ";
    $_SESSION['errors'] = $error;
    header("Location: ../addbid.php");
    return;
}

$dao_course = new CourseDAO();
$course_db = $dao_course->getCourse($course);

$dao_bid = new BidDAO();
$section_db = $dao_bid->retrieveSectionsByCourse($course);
$previous_bids = $dao_bid->getPreviousBids($userid);

$dao_coursecompleted = new CourseCompletedDAO;
$coursecompleted_db = $dao_coursecompleted->retrieveCoursesCompleted($userid);


$sql4 = "SELECT * FROM course_completed WHERE userid = :userid";
$stmt4 = $conn->prepare($sql4);
$stmt4->bindParam(':userid', $userid, PDO::PARAM_STR);
$stmt4->execute();
$courses_completed = [];
while ($row = $stmt4->fetch()){
    //retrieving all courses completed for the particular user - used to check for reqs and course completed later
    $courses_completed[] = new CourseCompleted($row['userid'], $row['code']);
}

foreach($courses_completed as $completed_course){//to find completed courses
    $dao_course_completed= new CourseCompletedDAO();
    $code_completed=$completed_course->getCode();
    if($code_completed==$course){
        //if the user bids for a course he/she has already completed
        $error[] = "Course already completed ";
        $_SESSION['errors'] = $error;
        header("Location: ../addbid.php");
        return;
    }
}

// var_dump($bid_db);
// var_dump($course_db);

echo "Previous Bids:<br>";

$error = [];

$course_exam_date = $course_db->getExamdate();
$course_exam_start = $course_db->getExamstart();
$course_exam_end = $course_db->getExamend();

if ($course_exam_date == null) { //check if course exists
    $error[] = "Enter a valid course";
    $_SESSION['errors'] = $error;
    header("Location: ../addbid.php");
    return;
}
// var_dump($course_exam_date);
// var_dump($course_exam_start);
// var_dump($course_exam_end);

$prerequisiteCheck = $dao_bid->clearPrerequisite($userid, $course);
// var_dump($prerequisiteCheck);
$school_db = $course_db->getSchool();
$round = $_SESSION['round']; 
if ($school != $school_db && $round == 1) {
    // If round is 1, and the user is bidding for a module not listed under their school
    $error[] = "Not available for bidding(bidded module must be from your own school)";
    $_SESSION['errors'] = $error;
    header("Location: ../addbid.php");
    return;

}
else { //Round 2 has no limitations to user's school and module school.
    foreach ($section_db as $availsection) {
        $biddedSection = $availsection->getSection();
        $sections[] = $biddedSection;
        if ($biddedSection == $section) { //Get all of the exam dates/timing and section day/timings for the bidded course & section
            $section_day = $availsection->getDay();
            $section_start = $availsection->getStart();
            $section_end = $availsection->getEnd();
            $section_size = $availsection->getSize();
            if ($section_size == 0){
                $error[] = "No vacancy.";
                $_SESSION['errors'] = $error;
                header("Location: ../addbid.php");
                return;
            }
            $biddedcourseExam = array($course_exam_date, $course_exam_start, $course_exam_end);
            $biddedsectionSchedule = array($section_day, $section_start, $section_end);

        }

    }

    if (in_array($section, $sections)) { //Checking if the section exists
        //Explode the array to compare numeric values
        $amount_array = explode(".", $amount);
        if (!is_numeric($amount) || $amount < 10 || (sizeof($amount_array) == 2 && strlen($amount_array[1])  > 2)) { //check if bid entered is numbers
            $error[] = "Not a valid bid amount";
            $_SESSION['errors'] = $error;
            header("Location: ../addbid.php");
            return;
        }
        
        $result = $dao_bid->getPreviousBids($userid); //result is the previous user bids
        
        foreach($result as $class){
            $code = $class->getCode();
            $status = $class->getStatus();
            
            if ($status=="Success" && $code==$course){
                // if the user has already bidded successfully for the course
                $error[]="Module enrolled";
                $_SESSION['errors'] = $error;
                header("Location: ../addbid.php");
                return;
            }
           
        }
        $limit = 5;
        

        if ($prerequisiteCheck == false) { //check for prerequisites
            $error[] = "Prerequisite not satisfied";
            $_SESSION['errors'] = $error;
            header("Location: ../addbid.php");
            return;
        }

        if (isset($coursecompleted_db)){ //check for course completion
            foreach($coursecompleted_db as $coursecompleted){
                if ($coursecompleted->getCode() == $course){
                    $error[] = "Course has been completed";
                    $_SESSION['errors'] = $error;
                    header("Location: ../addbid.php");
                    return;
                }
            }
        }
        $is_update = false;
        foreach ($result as $one_bid) { //previous user bids

            $one_bid_course = $one_bid->getCode();
            $one_bid_section = $one_bid->getSection();
            $one_bid_amount = $one_bid->getAmount();
            $one_bid_round = $one_bid->getRound();

            if ($one_bid_course == $course && $one_bid_round == $round) { //check if student has bidded for this course before within the same round
                $balance += $one_bid_amount;
                $is_update = true;
                $limit += 1;
            }
            if (count($result) == $limit) { //count number of modules bidded
                $error[] = 'Only a maximum of 5 courses can be bidded for';
                $_SESSION['errors'] = $error;
                header("Location: ../addbid.php");
                return;
            }

            echo "Course:$one_bid_course, Section:$one_bid_section, Amount bidded: $$one_bid_amount <br>";

            $result2 = $dao_bid->getCourseDetails($one_bid_course);
            $result3 = $dao_bid->getSectionDetails($one_bid_course, $one_bid_section);
    
            foreach ($result2 as $one_course) {
                if ($one_course->getCourse() != $course){ //If it's not a bid update (so that exam wont clash cause same module)
                    $one_course_examdate = $one_course->getExamdate();
                    $one_course_examstart = $one_course->getExamstart();
                    $one_course_examend = $one_course->getExamend();
                }
            }
            foreach ($result3 as $one_section) {
                if ($one_course->getCourse() != $course){
                    $one_section_day = $one_section->getDay();
                    $one_section_start = $one_section->getStart();
                    $one_section_end = $one_section->getEnd();
                }
            }
            if (isset($one_course_examdate) && isset($one_course_examstart) && isset($one_course_examend)){ //If there are any bids that are not the same course
                $previousbidsExam[] = array($one_course_examdate, $one_course_examstart, $one_course_examend);
            }
            if (isset($one_section_day) && isset($one_section_start) && isset($one_section_end)){
                $previousbidsSection[] = array($one_section_day, $one_section_start, $one_section_end);
            }
        }

        if (isset($previousbidsExam)) { //this is to check whether there was any previous bids
            foreach ($previousbidsExam as $one_previous_exam) {
                if($one_previous_exam[0] == $biddedcourseExam[0]){
                    //Explode the array to compare numeric values - [0] is hour, [1] is minute
                    $biddedexamstart = explode(":", $biddedcourseExam[1]);
                    $biddedexamend = explode(":", $biddedcourseExam[2]);
                    $previousexamstart = explode(":", $one_previous_exam[1]);
                    $previousexamend = explode(":", $one_previous_exam[2]);
                    if (((int)$biddedexamstart[0] > (int)$previousexamend[0]) || ((int)$biddedexamstart[0] == (int)$previousexamend[0] && (int)$biddedexamstart[1] >= (int)$previousexamend[1]) || ((int)$previousexamstart[0] > (int)$biddedexamend[0]) || ((int)$previousexamstart[0] == (int)$biddedexamend[0] && (int)$previousexamstart[1] >= (int)$biddedexamend[1])) { //check if a previous exams clashes
                        continue;
                    }
                    else{
                        $error[] = "Exam clashes";
                        $_SESSION['errors'] = $error;
                        header("Location: ../addbid.php");
                        return;
                    }
                }
            }
            foreach ($previousbidsSection as $one_previous_section) {
                if($one_previous_section[0] == $biddedsectionSchedule[0]){
                    //Explode the array to compare numeric values - [0] is hour, [1] is minute
                    $biddedexamstart = explode(":", $biddedsectionSchedule[1]);
                    $biddedexamend = explode(":", $biddedsectionSchedule[2]);
                    $previousexamstart = explode(":", $one_previous_section[1]);
                    $previousexamend = explode(":", $one_previous_section[2]);
                    if (((int)$biddedexamstart[0] > (int)$previousexamend[0]) || ((int)$biddedexamstart[0] == (int)$previousexamend[0] && (int)$biddedexamstart[1] >= (int)$previousexamend[1]) || ((int)$previousexamstart[0] > (int)$biddedexamend[0]) || ((int)$previousexamstart[0] == (int)$biddedexamend[0] && (int)$previousexamstart[1] >= (int)$biddedexamend[1])) { //check if a previous exams clashes
                        continue;
                    }
                    else{ //check if a previous section clashes
                        $error[] = "Section clashes";
                        $_SESSION['errors'] = $error;
                        header("Location: ../addbid.php");
                        return;
                    }
                    // var_dump($biddedexamstart);
                    // var_dump($biddedexamend);
                    // var_dump($previousexamstart);
                    // var_dump($previousexamend);
                }
                // var_dump($one_previous_section);
                // var_dump($biddedsectionSchedule);
            }
        } 
        
        else {
            echo "None<br>";
        }
        echo "<br>Current Bid:<br>Course:$course, Section:$section, Amount bidded: $$amount<br>";

        $balance = $balance - $amount;
        if ($balance < 0){
            $error[] = "Insufficient balance";
            $_SESSION['errors'] = $error;
            header("Location: ../addbid.php");
            return;
        }
        $_SESSION['edollar'] = $balance;
        // echo "<br>Successfully placed bid<br> You have $$balance left to bid.";

        $dao_bid->updateCredits($balance, $userid); //update the credits
        $successfulbid = new Bid($userid, $amount, $course, $section, 'pending', $round);
        foreach ($result as $one_bid) { //previous user bids

            $one_bid_course = $one_bid->getCode();
            $one_bid_section = $one_bid->getSection();
            $one_bid_amount = $one_bid->getAmount();
            $one_bid_round = $one_bid->getRound();
            if ((isset($one_bid_course) && $one_bid_course == $course) && (isset($one_bid_round) && $one_bid_round == $round) && $is_update) { //check if student has bidded for this course before within the same round
                $dao_bid->removeforround($userid,$one_bid_course, $one_bid_round);
            }
        }
        $result = $dao_bid->addforround($successfulbid);

        if ($_SESSION['round'] == 2){
            $roundtwologic = new RoundTwoLogic();
            $roundtwologic->processbids();
        }
            
           

        }
    else { 
        //check if section is proper
        $error[] = "No such section";
        $_SESSION['errors'] = $error;
        header("Location: ../addbid.php");
        return;
    }
} 


?>

<html>

<form action='../index.php' method='POST'>
        <input type="submit" value="Back to bidding">


</form>

</html>