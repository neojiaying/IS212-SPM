<?php

require_once 'common.php';

$student = '';
if  (isset($_SESSION['userid'])) {
	$student = $_SESSION['userid'];
}

# check if the username session variable has been set 
# send user back to the login page with the appropriate message if it was not
# add your code here 
if (!isset($_SESSION['userid'])) {
	header("Location:login.php?error=Please login.");
	exit;
}
?>