<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';

// If there are no errors on token

if (empty($error)){
    // Get the request from the URL
    $request = json_decode($_REQUEST['r'], true);

    $error=[];

    // Basic JSON validations (Missing and blank)
    if (isset($request['userid']) && empty($request['userid'])){
        $error[] = "blank userid";
    }
    if (!isset($request['userid'])){
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

        $userid = $request["userid"];
        $dao = new StudentDAO();
        $student = $dao->retrieve($userid);

        // check whether userid is valid

        if ($student == null){
            $message = [ "invalid userid" ];
            $result = [
                "status" => "error",
                "message" => $message
                    ];

            header('Content-Type: application/json');
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
            exit();

            
        }
        else{
            // no errors

            $edollar = (float)$student->getEdollar();

            $result = [
                "status" => "success",
                "userid" => $student->getUserid(),
                "password" => $student->getPassword(),
                "name" => $student->getName(),
                "school" => $student->getSchool(),
                "edollar" => $edollar
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














?>