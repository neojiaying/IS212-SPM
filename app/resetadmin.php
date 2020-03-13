<?php
require_once 'include/common.php';
// require_once 'include/protect.php';

$dao_defaultround = new RoundDAO();
$round = $dao_defaultround->defaultround();

if($round){
    unset($_SESSION['round']);
    unset($_SESSION['status']);
    echo "Default Round has been started.";
}
else{
    echo "Default Round has not been started.";
}


?>