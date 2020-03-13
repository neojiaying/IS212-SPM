<?php
require_once 'include/common.php';
require_once 'include/protectadmin.php';
// require_once 'include/protect.php';

$dao_startround = new RoundDAO();
$round = $dao_startround->startround();

if($round){
    $getround = $dao_startround->getDetails();
    $_SESSION['round'] = $getround[0]->getRound();
    $_SESSION['status'] = $getround[0]->getStatus();
    header('Location: admin_index.php?msg=Round has been started');
    return;
}
else{
    header('Location: admin_index.php?msg=Round has not been started');
    return;
}


?>
