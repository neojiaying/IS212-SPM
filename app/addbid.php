<?php 
    require_once 'include/common.php';
    require_once 'include/protect.php';

    $userid = $_SESSION['userid'];
    $school = $_SESSION['school'];

    $dao_student = new StudentDAO();
    $student = $dao_student->retrieve($userid);

    $name = $student->getName();
    $edollar = $student->getEdollar();
    $message =  "Hello, $name! You have a balance of $$edollar.  ";
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
   
      $minimum_bid = 10;
      $sectionDAO = new SectionDAO();
      $course_list = $sectionDAO->retrieveAll();
      echo "<div style='float: right;'>
          <h2>List of available courses:</h2>
          <table border=1>
              <tr>
              <th>Course</th>
              <th>Section</th>
              <th>Day</th>
              <th>Start</th>
              <th>End</th>
              <th>Instructor</th>
              <th>Venue</th>
              <th>Vacancy</th>";
      if ($_SESSION['round'] == 2){
          echo "<th>Minimum Bid</th>";
      }
      echo "</tr>";
      foreach ($course_list as $course_details){
        echo "<tr>
                <td align='center'>{$course_details->getCourse()}</td>
                <td align='center'>{$course_details->getSection()}</td>
                <td align='center'>{$course_details->getDay()}</td>
                <td align='center'>{$course_details->getStart()}</td>
                <td align='center'>{$course_details->getEnd()}</td>
                <td align='center'>{$course_details->getInstructor()}</td>
                <td align='center'>{$course_details->getVenue()}</td>
                <td align='center'>{$course_details->getSize()}</td>
                ";
        if ($_SESSION['round'] == 2){
            $minbidDAO = new MinBidDAO();
            $minimum_bid = (float)($minbidDAO->retrieve($course_details->getCourse(), $course_details->getSection())[0]->getMinBid());
            // $sql = "SELECT * FROM bid WHERE code = :code and section = :section and status = 'success' and round = 2 ORDER BY amount DESC";
            // $connMgr = new ConnectionManager();      
            // $conn = $connMgr->getConnection();
            // $stmt = $conn->prepare($sql);
            // $code = $course_details->getCourse();
            // $section = $course_details->getSection();
            // $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            // $stmt->bindParam(':section', $section, PDO::PARAM_STR);
            // if ($stmt->execute()){
            //   $result = [];
            //   while ($row = $stmt->fetch()){
            //     $result[] = new Bid($row['userid'], $row['amount'], $row['code'], $row['section'], $row['status'], $row['round']);
            //   }             
            // }
            // $sql1 = "SELECT * FROM bid WHERE code = :code and section = :section and status = 'fail' and round = 2 ORDER BY amount DESC";
            // $stmt1 = $conn->prepare($sql1);
            // $stmt1->bindParam(':code', $code, PDO::PARAM_STR);
            // $stmt1->bindParam(':section', $section, PDO::PARAM_STR);
            // if ($stmt1->execute()){
            //   $result1 = [];
            //   while ($row = $stmt1->fetch()){
            //     $result1[] = new Bid($row['userid'], $row['amount'], $row['code'], $row['section'], $row['status'], $row['round']);
            //   }
            // }
            // if ($result != []){
            //   if (sizeof($result) + sizeof($result1) >= $course_details->getSize()){
            //     if (sizeof($result) >= $course_details->getSize()){
            //       $check_price = $result[$course_details->getSize() - 1]->getAmount();
            //     }
            //     else{
            //       $check_price = $result1[$course_details->getSize() - sizeof($result) - 1]->getAmount();
            //     }
            //     if ((float)$check_price >= $minimum_bid){
            //       $minbidDAO->update($course_details->getCourse(), $course_details->getSection(), (float)$check_price + 1);
            //     }
            //   }
            // }
            echo "<td align='center'>$minimum_bid</td>";
            // $minimum_bid = 10; //reset the minimum bid back to 10
        }
        echo "</tr>";
      }
      echo "</table>
      </div>";
    // }
    ?>
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
        $bid_amount = $obj->getAmount();
        $bid_status = ucfirst($obj->getStatus());
        $bid_round = $obj->getRound();
        if ($_SESSION['round'] == 2){
          $sql = 'SELECT * FROM bid WHERE code = :code AND section = :section AND round = :round';
        
          $connMgr = new ConnectionManager();
          $conn = $connMgr->getConnection();
          
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':code', $bid_code, PDO::PARAM_STR);
          $stmt->bindParam(':section', $bid_section, PDO::PARAM_STR);
          $stmt->bindParam(':round', $_SESSION['round'], PDO::PARAM_INT);
          $stmt->execute();
          $result = [];
          while ($row = $stmt->fetch()){
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'], $row['section'], $row['status'], $row['round']);
          }
        }
        echo"<tr>                       
                <td align='center'>$bid_code</td>
                <td align='center'>$bid_section</td>
                <td align='center'>$bid_amount</td>
                <td align='center'> $bid_status</td>
                <td align='center'> $bid_round</td>
            </tr>";
      }
    }
   ?>
    </table>
            <h2> Bid for a Course </h2>
            <!-- Form for adding bids -->
            <?php
            // Only show form when round is 1 or 2 and active
            if (($_SESSION['round'] == "1" || $_SESSION['round'] == "2") && $_SESSION['status'] == 'active'){
                echo"
                    <form action='include/process_bid.php' method='POST'>
                        Course(eg. ISXXX):<input type='text' name='course'> <br><br>
                        Section(eg. SX):<input type='text' name='section'><br><br>
                        Amount:<input type='text' name='amount'><br><br>
                        <input type='submit' value='Bid'>
                    </form>";
            }
            else{
              // Display message as to why user is unable to add bid
              echo "Add bids is currently unavailable";
            }
            ?>

            <?php
              // display errors for why cannot bid
                  if (!empty($_SESSION['errors'])){
                    printErrors();
                  }

            ?>
</body>
</html>