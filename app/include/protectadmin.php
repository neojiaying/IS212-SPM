<?php

require_once 'common.php';

$admin = '';
if  (isset($_SESSION['admin'])) {
	$admin = $_SESSION['admin'];
}

# check if the username session variable has been set 
# send user back to the login page with the appropriate message if it was not
# add your code here 
if (!isset($_SESSION['admin'])) {
	header("Location:login.php?error=Access Denied!");
	exit;
}
?>