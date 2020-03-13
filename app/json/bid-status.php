<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';

if (empty($error)){ //check for any errors at the start
    $data = json_decode($_REQUEST["r"],true);

    $error = [];

    if (isset($data['course']) && empty($data['course'])){
        $error[] = "blank course";
    }
    if (!isset($data['course'])){
        $error[] = "missing course";
    }
    if (isset($data['section']) && empty($data['section'])){
        $error[] = "blank section";
    }
    if (!isset($data['section'])){
        $error[] = "missing section";
    }


    if (!isEmpty($error)) { //if there are errors, then display the errors
        $result = [
            "status" => "error",
            "message" => $error
            ];

        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
        exit();
    }  

    else{
        $course=strtoupper($data["course"]);
        $section=strtoupper($data["section"]);
        $round=$_SESSION['round'];
        $status=$_SESSION['status'];
        $error = [];
    

        $dao_course = new CourseDAO();
        $course_chk = $dao_course->courseExist($course);

        if ($course_chk==null){
            $error[] = "invalid course";
            
            $result = [
                "status" => "error",
                "message" => $error
            ];    
            header('Content-Type: application/json');
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
            exit();   
        }
        else{
            $dao_section = new SectionDAO();
            $section_arr = $dao_section->getSection($course);
            if(!in_array($section,$section_arr)){
                
                $error[] = "invalid section";
                $result = [
                    "status" => "error",
                    "message" => $error
                ];                     
                header('Content-Type: application/json');
                echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                exit();   
            }
        }
        if($round == '1' && $status == 'active'){ //for round 1 and active
            $dao_section = new SectionDAO();
            $vacancy1 = $dao_section -> getVacancy($course,$section); //get the class size of particular course and section in an array
            $vacancy = (int)$vacancy1[0]; // get class size
            
            $dao_bid = new BidDAO();
            $actual_bid = $dao_bid -> retrieveSpecificBidStatus1($course,$section); //get the actual number of people who bidded in round 1
            
            if (!empty($actual_bid)){
                if (sizeof($actual_bid) > $vacancy || sizeof($actual_bid) == $vacancy){ // if bidded people > class size or bidded people = class size
                    $correct = $vacancy - 1;
                    $clearing_price = $actual_bid[$correct] -> getAmount(); // get minimum bid            
                }

                if(sizeof($actual_bid) < $vacancy){ // if bidded people < class size
                    $lowest_bidder = $actual_bid[sizeof($actual_bid)-1];
                    $lowest_bid = $lowest_bidder -> getAmount();
                    $min_bid = (float)$lowest_bid;
                }
                else{
                    $min_bid = (float)$clearing_price;
                }  

                foreach($actual_bid as $one_bid ){ //get individual bids detail
                    $status = $one_bid -> getStatus();
                    $userid = $one_bid -> getUserid();
                    $dao_student = new StudentDAO();
                    $student = $dao_student -> retrieve($userid);
                    $student_bal = $student -> getEdollar();
                    
                        $message[] = [
                            "userid" => $userid,
                            "amount" => (float)$one_bid->getAmount(),
                            "balance"=> (float)$student_bal,
                            "status" => $status
                        ];
                    }  
           
                    $result = [
                        "status" => "success",
                        "vacancy" => $vacancy,
                        "min-bid-amount" => $min_bid,
                        "students" => $message
                    ];             
                }
                else{
                    $result = [
                        "status" => "success",
                        "vacancy" => $vacancy,
                        "min-bid-amount" => (float) "10.0",
                        "student"=>[ ]
                    ];

                }
            }    
            elseif ( $round == '1' && $status == 'inactive'){ // for round 1 and inactive
                $dao_section = new SectionDAO();
                $vacancy1 = $dao_section -> getVacancy($course,$section);
                $vacancy = (int)$vacancy1[0];
                
                $dao_bid = new BidDAO();
                $success_bid = $dao_bid -> retrieveBidByStatus1($course,$section,"Success"); // get successful bids in round 1 for specific mod
                $actual_bid = $dao_bid -> retrieveSpecificBidStatus1($course,$section);// get all bids in round 1 for specific mod
                if (!empty($success_bid)){ //there are successful bids
                $min_bid = (float)$success_bid [sizeof($success_bid) - 1] -> getAmount(); //get minimum successful bid

                foreach($actual_bid as $one_bid ){
                        $status = $one_bid -> getStatus();                    
                        $userid = $one_bid -> getUserid();
                        $dao_student = new StudentDAO();
                        $student = $dao_student -> retrieve($userid);
                        $student_bal = $student -> getEdollar();                    
                        $message[] = [
                            "userid" => $userid,
                            "amount" => (float)$one_bid->getAmount(),
                            "balance"=> (float)$student_bal,
                            "status" => $status
                        ];                                                            
                }        
                    $result = [
                        "status" => "success",
                        "vacancy" => $vacancy,
                        "min-bid-amount" => $min_bid,
                        "students" => $message
                    ];             
                }
                else{
                    $result = [
                        "status" => "success",
                        "vacancy" => $vacancy,
                        "min-bid-amount" => (float) "10.0",
                        "student"=>[ ]
                    ];

                    }
                } 
            elseif ( $round == '2' && $status == 'active') { // for round 2 and active
                $dao_section = new SectionDAO();
                $dao_minbid = new MinBidDAO(); //table for min bid 
                $vacancy1 = $dao_section -> getVacancy($course,$section);
                $vacancy = (int)$vacancy1[0];
                $min_bid_array = $dao_minbid -> retrieve($course,$section);
                $min_bid = $min_bid_array[0] -> getMinBid(); //get min bid of a particular course section
                
                $dao_bid = new BidDAO();
                $actual_bid = $dao_bid -> retrieveSpecificBidStatus2($course,$section);
                if (!empty($actual_bid)){
                foreach($actual_bid as $one_bid ){
                    $status = $one_bid -> getStatus();
                    $userid = $one_bid -> getUserid();
                    $dao_student = new StudentDAO();
                    $student = $dao_student -> retrieve($userid);
                    $student_bal = $student -> getEdollar();
                    
                        $message[] = [
                            "userid" => $userid,
                            "amount" => (float)$one_bid->getAmount(),
                            "balance"=> (float)$student_bal,
                            "status" => $status
                        ];
                    }  
                    $result = [
                        "status" => "success",
                        "vacancy" => $vacancy,
                        "min-bid-amount" => (float)$min_bid,
                        "students" => $message
                    ]; 
                }
                else{
                    $result = [
                        "status" => "success",
                        "vacancy" => $vacancy,
                        "min-bid-amount" => (float) "10.0",
                        "student"=>[ ]
                    ];
                }                
            }
            elseif ( $round == '2' && $status == 'inactive') { // for round 2 and inactive
                $dao_section = new SectionDAO();
                $vacancy1 = $dao_section -> getVacancy($course,$section);
                $vacancy = (int)$vacancy1[0];
                $dao_bid = new BidDAO();
                $actual_bid = $dao_bid -> retrieveAllSuccessBid($course,$section); // gets all sucessful bids
                if (!empty($actual_bid)){
                $min_bid = (float)$actual_bid [sizeof($actual_bid)-1] -> getAmount();
                foreach($actual_bid as $one_bid ){
                    $status = $one_bid -> getStatus();
                    $userid = $one_bid -> getUserid();
                    $dao_student = new StudentDAO();
                    $student = $dao_student -> retrieve($userid);
                    $student_bal = $student -> getEdollar();
                    
                        $message[] = [
                            "userid" => $userid,
                            "amount" => (float)$one_bid->getAmount(),
                            "balance"=> (float)$student_bal,
                            "status" => $status
                        ];
                    } 
                    $result = [
                        "status" => "success",
                        "vacancy" => $vacancy,
                        "min-bid-amount" => $min_bid,
                        "students" => $message
                    ]; 
                } 
            
            else{
                $result = [
                    "status" => "success",
                    "vacancy" => $vacancy,
                    "min-bid-amount" => (float) "10.0",
                    "student"=>[ ]
                ];

            }
            
        }
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
        exit(); 
           
        
            
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
exit();
?>