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

        $course = $request['course'];
        $section = $request['section'];

        $dao_bid = new BidDAO();
        $bids = $dao_bid->retrieveSpecificBidDumpBid($course,$section);

        $message=[];

        // If there are no bids under that course & section
        if (!empty($bids)){
            foreach($bids as $bid ){
                $status = $bid->getStatus();
                $roundbidded = $bid->getRound();
                if (($status == 'Success' && $_SESSION['round'] == 2 && $_SESSION['status'] == 'inactive') || ($status == 'Success' && $roundbidded == 1)){
                    $message[] = [
                        "userid" => $bid->getUserid(),
                        "amount" => (float)$bid->getAmount()
                    ];
                }  
            }

            $result = [
                "status" => "success",
                "students" => $message
            ];
        }
        else{
            // If there are bids for that course & section
            // Check which does not exist (course/section)

            $error=[];
            
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

            $result = [
                "status" => "error",
                "message" => $error
            ];
            if ($error == []){
                $result = [
                    "status" => "success",
                    "students" => [ ]
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
?>