<?php
require_once 'include/common.php';
$error = '';
if (isset($_GET['error'])) { //Error from users who have not logged in, but are trying to access other pages.
    $error = $_GET['error'];
} 
elseif (isset($_POST['login'])) { //If user submits the form, process it
    if ($_POST['username'] == 'admin') { //if the user is an administrator
        $daoAdmin = new AdminDAO();
        $admin = $daoAdmin->retrieve('admin');
        if ($admin != null && $_POST['password'] == $admin->getPassword()) { //ADMIN PW: faFpe4Exv!3
            $_SESSION['admin'] = 'admin';
            header('Location: admin_index.php'); 
            return;
        }
    }
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $dao = new StudentDAO();
        $student = $dao->retrieve($username);

        if ($student != null && $student->getPassword() == $password) {
            $_SESSION['userid'] = $student->getUserid();
            $_SESSION['school'] = $student->getSchool();
            header("Location: index.php");
            return;

        } 
        elseif ($username == "") {
            if ($username == "" && $password == "") {
                $error = "Incorrect username or password!";
            } 
            else {
                $error = "Incorrect username!";
            }
        } 
        elseif ($student == null) { //Student not found in database
            $error = 'Invalid user!';
        } 
        else {
            $error = "Incorrect password!";
        }

    }
}
?>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
        <div class='main'>
        <h1>Welcome to BIOS</h1>
        <h2>Login</h2>
        <!-- Form for login -->
        <form method='POST' action='login.php'>
            <table>
                <tr>
                    <td>Username</td>
                    <td>
                        <input name='username' />
                    </td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>
                        <input name='password' type='password' />
                    </td>
                </tr>
                <tr>
                    <td colspan='2' style="text-align: center;">
                        <input name='login' type='submit' value= "Login" />
                    </td>
                </tr>
            </table>
        </form>
        <p>
            <?=$error?>
        </p>


        </div>
    </body>
</html>

<!-- CSS -->

<style>
    .main{
        display: inline-block;
    }
    body{
        text-align: center;
    }
</style>