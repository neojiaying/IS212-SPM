<?php
require_once 'common.php';

$userid = $_SESSION['userid'];
$active=$_SESSION['status'];
$section = strtoupper($_POST['section']);
$course = strtoupper($_POST['course']);

$dao_student = new StudentDAO();
$student = $dao_student->retrieve($userid);

$name = $student->getName();
$balance = $student->getEdollar();


$dao_bids = new BidDAO();
$bid_db = $dao_bids->getPreviousBids($userid);


if($active == "inactive"){
    //if the round is inactive, the bids have already been processed
    $error[] = "Unable to drop bid";
    $_SESSION['errors'] = $error;
    header("Location: ../dropsection.php");
    return;

}

if (empty($bid_db)){
    //if the user has no bids
    $error[] = "Unable to drop bid";
    $_SESSION['errors'] = $error;
    header("Location: ../dropbid.php");
    return;
}

$count = 0;
foreach ($bid_db as $bid) {
    $bid_code=$bid->getCode();
    $bid_section = $bid ->getSection();
    $bid_amt = $bid->getAmount();
    $bid_status = $bid->getStatus();

    if($bid_status=='pending' && $bid_code==$course && $bid_section==$section){
        $count++;
        $dao_bids->remove($userid,$course);
        $final_balance = $balance + $bid_amt;
        $update_balance = $dao_bids-> updateCredits($final_balance,$userid);
        
        echo "Successfully removed bid<br> You have been refunded $$bid_amt.";
        if ($_SESSION['round'] == 2){
            $roundtwologic = new RoundTwoLogic();
            $roundtwologic->processbids();
        }
    }
}
if ($count == 0){
    //the user did not bid for that particular course/section
    $error[] = "Unable to drop bid";
                $_SESSION['errors'] = $error;
                header("Location: ../dropbid.php");
                return;
}


?>
<html>
<form action='../index.php' method='POST'>
    <input type="submit" value="Back to bidding">
</html>