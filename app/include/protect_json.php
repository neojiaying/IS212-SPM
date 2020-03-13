<?php
require_once 'token.php';
require_once 'common.php';

$token = '';
$error=[];

if  (isset($_REQUEST['token'])) {
	$token = $_REQUEST['token'];
	if (empty($token)){
		$error = ['blank token'];
	}else{
		if (!verify_token($token)){
			$error = [ 'invalid token' ];
		}
	}
}
else{
	$error=['missing token'];
}



# check if token is not valid
# reply with appropriate JSON error message

# add your code here 



# this bit below might be useful for protecting the JSON requests and for your project 
# it will help to check for more conditions such as 

# if the user is not an admin and trying to access admin pages

# if the user is trying to access json services and is not doing it properly

# $pathSegments = explode('/',$_SERVER['PHP_SELF']); # Current url
# $numSegment = count($pathSegments);
# $currentFolder = $pathSegments[$numSegment - 2]; # Current folder
# $page = $pathSegments[$numSegment -1]; # Current page

# you can do things like If ($page == "bootstrap-view.php) {   or 
# if ($currentfolder == "json") {  

?>