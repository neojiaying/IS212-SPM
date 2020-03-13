<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';

// If there are no errors on token

if (empty($error)){
    // Get the request from the URL
    $request = json_decode($_REQUEST["r"],true); 

    $error=[];

    // Basic JSON validations (Missing and blank)
    if (isset($request['course']) && empty($request['course'])){
        $error[] = "blank course";
    }
    
    if (!isset($request['course'])){
        $error[] = "missing course";
    }
    if (isset($request['section']) && empty($request['section'])){
        $error[] = "blank section";
    }
    if (!isset($request['section'])){
        $error[] = "missing section";
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

        $course=$request["course"];
        $section=$request["section"];

        $dao_bid = new BidDAO();
        $bid = $dao_bid->retrieveSpecificBidDumpBid($course,$section);

        // Check if there are any bids for the course & section

        if ($bid){
            $message = [];
            //$count = 0
            foreach ($bid as $bid_obj){
                
                
                $userid = $bid_obj->getUserid();
                $amount = $bid_obj->getAmount();
                $status = $bid_obj->getStatus();
                $bidround = $bid_obj->getRound();

                if ($_SESSION['status'] == 'active'){
                    $status = '-';
                }
                elseif ($status == 'Success'){
                    $status = 'in';
                }
                else{
                    $status = 'out';
                }
                if ($bidround == $_SESSION['round']){
                    $message[] = [
                                "row" => 0,
                                "userid" => $userid,
                                "amount" => (float)$amount,
                                "result" => $status
                            ];
                }
                
            }

            $overall_message = [];

            foreach($message as $key => $row){
                $overall_message[$key] = $row['amount'];
            }


            array_multisort($overall_message, SORT_DESC, $message);

            for($i = 1; $i <= count($message); $i++){
                $message[$i-1]['row'] = $i;
            }

            $result = [
                "status" => "success",
                "bids" => $message
            ];
        }
        // If there are bids for that course & section
        // Check which does not exist (course/section)
        else{
            $error=[];

            $dao_course = new CourseDAO();
            $course_chk = $dao_course->courseExist($course);

            $dao_section = new SectionDAO();
            $section_arr = $dao_section->getSection($course);

            if ($course_chk==null){
                $error[] = "invalid course";
            }


            // Display "invalid section" only when course is not valid
            // Else the error will only show "invalid course"
            elseif (!in_array($section,$section_arr)){
                $error[] = "invalid section";
            }

            $result = [
                "status" => "error",
                "message" => $error
            ];
        }


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