<?php
require_once 'common.php';
$userid = $_SESSION['userid'];
$active=$_SESSION['status'];
$round=$_SESSION['round'];
$section = strtoupper($_POST['section']);
$course = strtoupper($_POST['course']);
// $balance = $_SESSION['edollar'];

$dao_student = new StudentDAO();
$student = $dao_student->retrieve($userid);

$name = $student->getName();
$balance = $student->getEdollar();

$dao_bids = new BidDAO();
$bid_db = $dao_bids->getPreviousBids($userid);

if($active == "active" && $round == '1' ){
    //Bids have not been processed
    $error[] = "Unable to drop bids that are pending";
    $_SESSION['errors'] = $error;
    header("Location: ../dropsection.php");
    return;

}

if($active == "inactive"){
    //Bids have already been processed
    $error[] = "Unable to drop section";
    $_SESSION['errors'] = $error;
    header("Location: ../dropsection.php");
    return;

}



if (empty($bid_db)){
    //No successful bid to drop
    $error[] = "Unable to drop section";
    $_SESSION['errors'] = $error;
    header("Location: ../dropsection.php");
    return;
}

$count = 0;
foreach ($bid_db as $bid) {
    $bid_code=$bid->getCode();
    $bid_section = $bid ->getSection();
    $bid_amt = $bid->getAmount();
    $bid_status=$bid->getStatus();
    $bid_round = $bid->getRound();

    
    if($bid_code==$course && $bid_section==$section && $bid_status == 'Success'){
        $count++;
        $dao_bids->removeforround($userid,$course,$bid_round);
        // $_SESSION['edollar'] = $balance+$bid_amt;
        $final_balance = $balance + $bid_amt;
        $update_balance = $dao_bids-> updateCredits($final_balance,$userid);
        echo "Successfully removed bid<br> You have been refunded $$bid_amt.";
        if ($round == 2){
            if ($bid->getRound() == 1){
                $sql = "UPDATE section SET size = size + 1 WHERE course = :course and section = :section";
                $connMgr = new ConnectionManager();      
                $conn = $connMgr->getConnection();
                $stmt = $conn->prepare($sql);  
                $stmt->bindParam(':course', $bid_code, PDO::PARAM_STR);
                $stmt->bindParam(':section', $bid_section, PDO::PARAM_STR);
                $stmt->execute();     
            }
        }
        
    }

}
if ($count == 0){
    //if course/section is not found
    $error[] = "Unable to drop section";
    $_SESSION['errors'] = $error;
    header("Location: ../dropsection.php");
    return;
}
   




?>

<html>
<form action='../index.php' method='POST'>
    <input type="submit" value="Back to bidding">
</html>