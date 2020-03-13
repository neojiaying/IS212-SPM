<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';

// If there are no errors on token

if (empty($error)){  
    $dao_startround = new RoundDAO();

    if ($_SESSION['status'] == 'active' && $_SESSION['round'] < 3){ //Means that round has already been initialized
        $result = [
            "status" => "success",
            "round" => (int)$_SESSION['round']
        ];
    }


    else {
        if ($_SESSION['round'] >= 2){
            $result = [
                "status" => "error",
                "message" => [ "round 2 ended" ]
            ];
        }
        else{
            $round = $dao_startround->startround();
            if($round){
                // $getround = $dao_startround->getDetails();
                $_SESSION['round'] += 1;
                $result = [
                    "status" => "success",
                    "round" => (int)$_SESSION['round']
                ];
                // $_SESSION['round'] = $getround[0]->getRound();
                // $_SESSION['status'] = $getround[0]->getStatus();
            }
            
            
            
            
            else{
                $result = [
                            "status" => "error",
                            "message" => [ "error message" ]
                        ];
            }
        }
        
        // else{
        //     $result = [
        //         "status" => "error",
        //         "message" => [ "error message" ]
        //     ];
        // }
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
