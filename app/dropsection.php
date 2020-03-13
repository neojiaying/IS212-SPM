<?php 
    require_once 'include/common.php';
    require_once 'include/protect.php';

// Do not let them enter this page if the round is invalid
    if ($_SESSION['round'] > 2){ 
        header("Location: index.php");
        return;
    }

    $userid = $_SESSION['userid'];
    $school = $_SESSION['school'];

    $dao_student = new StudentDAO();
    $student = $dao_student->retrieve($userid);

    $name = $student->getName();
    $edollar = $student->getEdollar();

    $message =  "Hello, $name! You have a balance of $$edollar. ";
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/webpages.scss">
</head>
<body>
    <div class='test'>
        <?= $message ?>
    </div>

    <br>
    <a href="index.php">Home</a>
    <a href="addbid.php">Bid for a course</a>
    <a href="dropbid.php">Drop bid</a>
    <a href="dropsection.php">Drop section</a>
    <a href="logout.php">Logout</a>
<?php
    // Display the round and status
    if(!isset($_SESSION['round'])){
                echo"<h3> Current Round: NIL <br> Status: NIL</h3>";
            }

        else{
            $round=$_SESSION['round'];
            $status=ucfirst($_SESSION['status']);
            echo"<h3> Current Round: $round <br> Status: $status</h3>";

        }
?>
 <!-- View bidding results -->
    Bidded Courses:<br>
    <table border = 1>
    <tr>
        <th> Code </th>
        <th> Section </th>
        <th> Amount </th>
        <th> Status </th>
        <th> Round </th>
    </tr>

    <?php
    $dao_bids = new BidDAO();
    $bids_db = $dao_bids->getPreviousBids($userid);

    if($bids_db == []) { //If a student has no bids yet
      echo"<tr>                       
              <td align='center'>NIL</td>
              <td align='center'>NIL</td>
              <td align='center'>NIL</td>
              <td align='center'>NIL</td>
              <td align='center'>NIL</td>
          </tr>";
    }
    
    else {
     

      foreach ($bids_db as $obj) {
        $bid_code = $obj->getCode();
        $bid_section = $obj->getSection();
        $bid_amount=$obj->getAmount();
        $bid_status=ucfirst($obj->getStatus());
        $bid_round= $obj->getRound();
        echo"<tr>                       
                <td align='center'>$bid_code</td>
                <td align='center'>$bid_section</td>
                <td align='center'>$bid_amount</td>
                <td align='center'> $bid_status</td>
                <td align='center'>$bid_round</td>
            </tr>";
      }
    }    
    ?>
    </table>

</body>

<h2> Drop Section </h2>
<!-- Form for dropping section -->
<?php
        
        if (($_SESSION['round'] == "2") && $_SESSION['status'] == 'active'){
             // Only show form when round is 2 and active (If round is 1 and active, only dropping bids is allowed)
            
            echo"<form action='include/process_dropsection.php' method='POST'>
                 Course(eg. ISXXX):<input type='text' name='course'> 
                 <br>
                 Section Number (eg. SX): <input type='text' name='section'> 
                 <br>";
            
            if (!empty($_SESSION['errors'])){
                printErrors();
            }
        
             echo"<input type='submit' value='Confirm'>
                  </form>";
        }


        
        
        else{
            // Display message as to why user cannot drop section
            echo "Drop section is currently unavailable";
        }

?>
      

</html>

