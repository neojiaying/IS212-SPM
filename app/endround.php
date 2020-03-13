<?php
require_once 'include/common.php';
require_once 'include/protectadmin.php';

$dao_endround = new RoundDAO();
$round = $dao_endround->endround();

if($round){
    $getround = $dao_endround->getDetails();
    $_SESSION['status'] = $getround[0]->getStatus();
    echo "Round has ended.";
    
}
else{
    echo "Round is still active.";
}


?>

<html>
<form action='admin_index.php' method='POST'>
    <input type='submit' value='Back to Admin Index'>
</form>