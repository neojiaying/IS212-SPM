<!-- THIS FORM IS TO GENERATE TOKEN FOR ADMIN -->

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
        <h1>JSON Authentication Form</h1>
        <form method='POST' action='authenticate.php'>
            <table>
                <tr>
                    <td>Username</td>
                    <td>
                        <input name='username' value='' />
                    </td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>
                        <input name='password' type='password' value='' />
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <input name='Login' type='submit' />
                    </td>
                </tr>
            </table>             
        </form>

        <?php 
        // If there are any errors from the form
        if(isset($_GET['error'])){echo $_GET['error'];}
        ?>

    </body>
</html>