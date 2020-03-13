<?php
require_once 'include/common.php';

session_unset();

session_destroy();

header("Location: login.php");
return;
