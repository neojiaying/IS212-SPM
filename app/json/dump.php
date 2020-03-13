<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';

if (empty($error)){


        $round=$_SESSION['round'];
        $status=$_SESSION['status'];
        
        $dao_course = new CourseDAO();
        $course_all = $dao_course->retrieveAll();
        $course=[];
        foreach ($course_all as $course_individual){ //get individual course details
            $courses = $course_individual -> getCourse();
            $school =  $course_individual -> getSchool();
            $title = $course_individual -> getTitle();
            $description = $course_individual -> getDescription();
            $examdate = $course_individual -> getExamdate();
            $examstart = date("Gi", strtotime($course_individual->getExamstart()));
            $course_individual->setExamStart($examstart);
            $examend = date("Gi", strtotime($course_individual->getExamend())); 
            $course_individual->setExamEnd($examend);
            $course[] = [ 
                "course" =>  $courses,
                "school" => $school,
                "title" => $title,
                "description" => $description,
                "exam date" => $examdate,
                "exam start" => $examstart,
                "exam end" => $examend
        ];

        }
        $dao_student = new StudentDAO();
        $student = $dao_student->retrieveAll();
        $student_array=[];
        foreach ($student as $one_student){
            $userid = $one_student->getUserid();
            $password = $one_student->getPassword();
            $name = $one_student->getName();
            $school = $one_student->getSchool();
            $edollar = $one_student->getEdollar();

            $student_array[] = ["userid"=>$userid,
                            "password"=>$password,
                            "name"=>$name,
                            "school"=>$school,
                            "edollar"=>(float)$edollar
                            ];
        }



        $dao_coursecompleted = new CourseCompletedDAO();
        $coursecompleted = $dao_coursecompleted->retrieveAll();


        $dao_prerequisite = new PrerequisiteDAO();
        $prerequisite = $dao_prerequisite->retrieveAll();

        
        $bid_array=[];
        if($round==2){
            $dao_bid = new BidDAO();
            $bid = $dao_bid-> retrieveAllforDump();
            foreach($bid as $one_bid){
                $userid = $one_bid->getUserid();
                $amount = $one_bid->getAmount();
                $code = $one_bid->getCode();
                $section = $one_bid->getSection();
                $bid_round = $one_bid->getRound();
                // $bid_array[] = $_SESSION['round'];
                // $bid_array[] = $bid_round;
                if ($bid_round == 2){ // if round is 2
                $bid_array[] = ["userid"=>$userid,
                                "amount"=>(float)$amount,
                                "course"=>$code,
                                "section"=>$section];
                }
            }
        }
        else{ //if round 1
            $dao_bid = new BidDAO();
            $bid = $dao_bid-> retrieveforDump();
            foreach ($bid as $one_bid){
                $userid = $one_bid->getUserid();
                $amount = $one_bid->getAmount();
                $code = $one_bid->getCode();
                $section = $one_bid->getSection();
                $bid_array[] = ["userid"=>$userid,
                                "amount"=>(float)$amount,
                                "course"=>$code,
                                "section"=>$section];
            }

        }
      
        $dao_bid = new BidDAO();
        $bid_1 = $dao_bid -> retrieveAllForDump();
        // $bid_status = $bid_1 -> getStatus();
        // $bid_round = $bid_1 -> getRound();
        $section_student_array1=[];
        $section_student_array2=[];
        foreach($bid_1 as $obj){ 
            $bid_status = $obj->getStatus();
            $userid = $obj->getUserid();
            $code=$obj->getCode();
            $section=$obj->getSection();
            $amount=$obj->getAmount();
            $bid_round=$obj->getRound();

            if ($bid_status=='Success'){
                $section_student_array1[] = ["userid"=>$userid,
                                            "course"=>$code,
                                            "section"=>$section,
                                            "amount"=>(float)$amount
                                            ];
            }
            // elseif ($bid_status=='Success' && $bid_round =='2'){
            //     $section_student_array2[] = ["userid"=>$userid,
            //                                 "course"=>$code,
            //                                 "section"=>$section,
            //                                 "amount"=>(float)$amount
            //                                 ];
            // }

        }



        $dao_section = new SectionDAO();
        $section = $dao_section->retrieveAll();
        foreach ($section as $section_individual){
            $temp = (int)$section_individual->getDay();
            $day_array = [1,2,3,4,5,6,7]; // convert day to day name
            $dayname_array = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $day = $dayname_array[array_search($temp, $day_array)];
            $section_individual->setDay($day);
            $start = date("Gi", strtotime($section_individual->getStart()));
            $section_individual->setStart($start);
            $end = date("Gi", strtotime($section_individual->getEnd()));
            $section_individual->setEnd($end);
            $section_individual->setSize((int)$section_individual->getSize());   
        }

        if($course == []){
            $course = [ ];
        }
        if($section == []){
            $section = [ ];
        }
        if($student_array == []){
            $student_array = [ ];
        }
        if($prerequisite == []){
            $prerequisite = [ ];
        }
        if($bid_array == []){
            $bid_array = [ ];
        }
        if($coursecompleted == []){
            $coursecompleted = [ ];
        }
       
        if (($section_student_array1 !=[]) ){
            $result = [
                "status" => "success",
                "course" => $course,
                "section" => $section,
                "student" => $student_array,
                "prerequisite" => $prerequisite,
                "bid" => $bid_array,
                "completed-course" => $coursecompleted,
                "section-student"=> $section_student_array1
                
            ];
        }
        // elseif ($section_student_array1 !=[] && $round == '2' && $status == 'active'){
        //     $result = [
        //         "status" => "success",
        //         "course" => $course,
        //         "section" => $section,
        //         "student" => $student_array,
        //         "prerequisite" => $prerequisite,
        //         "bid" => $bid_array,
        //         "completed-course" => $coursecompleted,
        //         "section-student"=> $section_student_array1
                
        //     ];
        // }
        // elseif ($section_student_array2 !=[] && $round == '2'){
        //     $result = [
        //         "status" => "success",
        //         "course" => $course,
        //         "section" => $section,
        //         "student" => $student_array,
        //         "prerequisite" => $prerequisite,
        //         "bid" => $bid_array,
        //         "completed-course" => $coursecompleted,
        //         "section-student"=> $section_student_array1
                
        //     ];
        // }
        else{
            $result = [
                "status" => "success",
                "course" => $course,
                "section" => $section,
                "student" => $student_array,
                "prerequisite" => $prerequisite,
                "bid" => $bid_array,
                "completed-course" => $coursecompleted,
                "section-student"=> [ ]
            
            ];
        }
    

    }
else{
    $result = [
        "status" => "error",
        "message" => $error
        ];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);


?>