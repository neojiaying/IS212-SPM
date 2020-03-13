<?php

require_once '../include/common.php';
require_once '../include/token.php';

// Basic validations for JSON (Blank and Missing)
// Alphabetically

$errors = [];
if (isset($_POST['password']) && empty($_POST['password'])){
    $errors[] = "blank password";
}
if (!isset($_POST['password'])){
    $errors[] = "missing password";
}
if (isset($_POST['username']) && empty($_POST['username'])){
    $errors[] = "blank username";

}
if (!isset($_POST['username'])){
    $errors[] = "missing username";
}
if (!isEmpty($errors)) {
    $result = [
        "status" => "error",
        "message" => $errors
        ];
}
else{

// If there are no error, get the username and password via POST from the form

    $username = $_POST['username'];
    $password = $_POST['password'];


// Check if username and password are correct
// Generate a token and return it in proper json format

    $daoAdmin = new AdminDAO();
    $admin = $daoAdmin->retrieve($username);
    if ($admin != null && $password == $admin->getPassword()) { //ADMIN PW: faFpe4Exv!3
        $result = [
            "status" => "success",
            "token" => generate_token($username)
        ];

        $_SESSION['token'] = generate_token($username);
    }

    // If username/password is wrong, display the respective errors
    
    elseif ($username != 'admin'){
        $result = [
            "status" => "error",
            "message" => [ "invalid username" ]
            ];
    }
    else{
        $result = [
            "status" => "error",
            "message" => [ "invalid password" ]
            ];
    }
}


header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);

?>