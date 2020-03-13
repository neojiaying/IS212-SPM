<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';
//NOTE: To do: Bid too low, no vacancy

// If there are no errors on token

if (empty($error)){
    // Get the request from the URL
    $request = json_decode($_REQUEST["r"],true);

    $error = [];

    // Basic JSON validations (Missing and blank)
    if (isset($request['amount']) && empty($request['amount'])){
        $error[] = "blank amount";
    }
    if (!isset($request['amount'])){
        $error[] = "missing amount";
    }
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
    if (isset($request['userid']) && empty($request['userid'])){
        $error[] = "blank userid";
    }
    if (!isset($request['userid'])){
        $error[] = "missing userid";
    }

    
    
    if ($error != []){
        $result = [
            "status" => "error",
            "message" => $error
        ];
    }
    else{

        // check the round, if round has error, only display round error

        if ($_SESSION['status'] == 'inactive'){
            $result = [
                "status" => "error",
                "message" => [ "round ended" ]
            ];
        }
        else{
            // Get the request from the URL
            
            $amount = $request['amount'];
            $course = $request['course'];
            $section = $request['section'];
            $userid = $request['userid'];
            $bid = new Bid($userid, $amount, $course, $section, 'pending', $_SESSION['round']);

            $dao_bid = new UpdateBidJsonDAO();
            $message = $dao_bid->add($bid, $_SESSION['round']);
            
            if (is_array($message)){
                $result = [
                    "status" => "error",
                    "message" => $message
                ];
            }
            elseif ($message == true){
                $result = [
                    "status" => "success"
                ];
                if ($_SESSION['round'] == 2){
                    $roundtwologic = new RoundTwoLogic();
                    $roundtwologic->processbids();
                }
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