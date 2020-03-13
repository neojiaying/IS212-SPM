<?php
require_once 'common.php';
require_once 'protectadmin.php';

$dao_endround = new RoundDAO();
 $round = $dao_endround->endround();
 
 if($round){
     $getround = $dao_endround->getDetails();
     $_SESSION['status'] = $getround[0]->getStatus();
     
 }
 $dao_round2 = new RoundTwoLogic();
 $dao_round2 -> processbidsEndRound2();
 
 
 header("Location: ../admin_index.php?msg=Round 2 ended");
 return;
?>