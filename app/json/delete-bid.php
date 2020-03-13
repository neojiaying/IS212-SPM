<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';

// If there are no errors on token

if (empty($error)){
        // Get the request from the URL
        $data = json_decode($_REQUEST["r"],true);

        $error = [];
        // Basic JSON validations (Missing and blank)
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
        if (isset($data['userid']) && empty($data['userid'])){
            $error[] = "blank userid";
        }
        if (!isset($data['userid'])){
            $error[] = "missing userid";
        }

        if (!isEmpty($error)) {
            $result = [
                "status" => "error",
                "message" => $error
                ];
        }
        else{
            // No errors
            // Get the request from the URL

            $userid = $data["userid"];
            $course = $data["course"];
            $section = $data["section"];

            // Check whether course/section/userid/round are valid 

            $dao_course = new CourseDAO();
            $course_chk = $dao_course->courseExist($course);

            $dao_section = new SectionDAO();
            $section_arr = $dao_section->getSection($course);

            if ($course_chk==null){
                $error[] = "invalid course";
            }

            elseif (!in_array($section,$section_arr)){
                $error[] = "invalid section";

            }

            $dao_student = new StudentDAO();
            $student = $dao_student->retrieve($userid);

            if ($student == null){
                $error[] = "invalid userid";

            }

            $round_dao = new RoundDAO();
            $round = $round_dao->getDetails();


            foreach($round as $obj){
                $status = $obj->getStatus();
                if ($status == 'inactive'){
                    $error[] = "round ended";
                }
            }

            if ($error != []){
                $result = [
                    "status" => "error",
                    "message" => $error
                ];

                header('Content-Type: application/json');
                echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                exit();

            }
            else{

                $dao_bid = new BidDAO();
                $bid = $dao_bid->retrieveSpecificBid($course,$section);
                $previous_bid = $dao_bid->getPreviousBids($userid);

                foreach($previous_bid as $detail){
                    $status = $detail->getStatus();
                }
                // student can only delete bids when bids are in pending status
                if ($status=='pending'){

                        if (empty($previous_bid)){
                            // if the user has never bidded for this course
                            $message = [ "no such bid" ];
                            $result = [
                                    "status" => "error",
                                    "message" => $message
                                    ];

                            header('Content-Type: application/json');
                            echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                            exit();       
                        }

                        

                        else{
                            // if the bid is in database, delete it and return edollar to user
                            $bid_remove = $dao_bid->remove($userid, $course);
                            $balance = $student->getEdollar();

                            foreach($previous_bid as $detail){
                                $bid_amt = $detail->getAmount();
                            }

                            $final_balance = $balance + $bid_amt;
                            $update_balance = $dao_bid-> updateCredits($final_balance,$userid);
                            $result = [
                                "status" => "success"
                            ];
                        

                        }
                }
                else{
                    // unsucessful response

                    $message = [ "no such bid" ];
                    $result = [
                                "status" => "error",
                                "message" => $message
                                ];
                }
            }
        }
    }else{
        
        $result = [
            "status" => "error",
            "message" => $error
            ];
    }


header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
exit();


?>