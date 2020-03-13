<html>
<body>
    <form action="admin_index.php">
        <input type="submit" value="Back"></button>
    </form>
</body>
</html>
<?php
require_once 'include/common.php';
require_once 'include/bootstrapUser.php';

$result = doBootstrap();
if($_POST['errors'] == [ 'input files not found' ]){
    echo "The necessary input files are not found.";
}
else{
    if($_POST['errors'] != []){
        echo"<h1>You have the following errors</h1>";
        echo "<table border=1>
            <tr>
            <th>File</th>
            <th>Line</th>
            <th>Message</th>
            </tr>";
        foreach($_POST['errors'] as $error){
            echo "<tr>
                    <td>{$error['file']}</td>
                    <td>{$error['line']}</td>
                    <td>";
            $messages = $error['message'];
            for($i = 0; $i < sizeof($messages); $i++){
                echo "{$messages[$i]}<br>";
            }
            echo"</td>
                </tr>";
        }
        echo "</table>";
    }

    echo "<h1>The following bids were processed</h1>";
    echo "<table border=1>
            <tr>
                <td>Bids processed</td>
                <td>{$_POST['bid_processed']}</td>
            </tr>
            <tr>
                <td>Courses processed</td>
                <td>{$_POST['course_processed']}</td>
            </tr>
            <tr>
                <td>Course Completed processed</td>
                <td>{$_POST['course_completed_processed']}</td>
            </tr>
            <tr>
                <td>Prerequisite processed</td>
                <td>{$_POST['prerequisite_processed']}</td>
            </tr>
            <tr>
                <td>Section processed</td>
                <td>{$_POST['section_processed']}</td>
            </tr>
            <tr>
                <td>Student processed</td>
                <td>{$_POST['student_processed']}</td>
            </tr>";
}




