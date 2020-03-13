<?php
require_once '../include/bootstrap.php';
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';


if (empty($error)){  
  $result = doBootstrap();
}


// header('Content-Type: application/json');
// echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
  

?>
