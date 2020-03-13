<?php
require_once 'include/common.php';
require_once 'include/protectadmin.php';

$connMgr = new ConnectionManager();
$conn = $connMgr->getConnection();
$sqlround = "SELECT * FROM round";
$stmtround = $conn->prepare($sqlround);
$stmtround->execute();
$roundset = [];
while ($row = $stmtround->fetch()){
    $roundset = new Round($row['round'], $row['status']);
}
$_SESSION['round'] = $roundset->getRound();
?>

<html>

<header>Hello Admin!</header>
<br><br>

<a href="include/process_round1.php">End Round 1</a>
<a href="startround.php">Start Round 2</a>
<a href="include/process_round2.php">End Round 2</a>

<a href="logout.php">Logout</a>

<br><br>
<h2> Bootstrap (Start Round 1) </h2>
<br>

<body>
    <form id='bootstrap-form' action="bootstrap-process.php"  method="post" enctype="multipart/form-data">
      File:
      <input id='bootstrap-file' type="file" name="bootstrap-file"><br>
      <input type="submit" value="Bootstrap" />
    </form>   
    <br>

</body>
<?php
// Displays message when a button is clicked, for eg. start round, end round
if (isset($_GET['msg'])){
  echo $_GET['msg'];
}

?>
        

</html>