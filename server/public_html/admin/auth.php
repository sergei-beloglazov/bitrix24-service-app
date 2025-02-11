<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/../settings/.admin_settings.php");

/**
 * Checks authorization.
 * Shows authorization dialog if user is not authorized
 *
 * @return bool if authorization 
 * 
 */
function auth()
{
    //Start session
    session_start();

    //Check authorization
    if ($_SESSION["AUTHORIZED"] === true) {
        return true;
    }
    //Wrong login flag
    $loginError = false;

    //Check if the login form was send
    if ($_POST["submit"] == "y") {
        //Check password
        if ((strlen(ADMIN_PANEL_PASSWORD) > 0)
            && (strtolower($_POST["login"]) == "admin")
            && ($_POST["password"] == ADMIN_PANEL_PASSWORD)
        ) {
            //Auth is OK, store in the session
            $_SESSION["AUTHORIZED"] = true;
            //Make Post-Redirect-Get
            header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
            exit();
        }
        //Wrong login/password
        $loginError = true;
    } // /Check if the login form was send


    //Show login form
?>
    <style>
        .loginform h1 {
            margin: 0 0 10px 0;
        }

        @media (min-width: 500px) {
            .loginform {
                max-width: 500px;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                /**FOR DEMO ONLY**/
                margin: 0;
                padding: 20px 30px 30px;
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
                background: white;
                /**FOR DEMO ONLY**/
            }
        }

        @media (max-width: 499px) {
            .loginform {
                /**FOR DEMO ONLY**/
                padding: 30px 30px 60px;
                background: rgba(12, 13, 14, 0.055);
                /**FOR DEMO ONLY**/
                min-width: 100%;
            }
        }
    </style>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />

    <form class="loginform" id="loginform" method="POST">
        <input type="hidden" name="submit" value="y">
        <h1 class="text-center">Admin access</h1>
        <?php
        if ($loginError) {
            echo "<b><span style='color:red'>Access denied</span><br><br>";
        }
        ?>
        <div class="form-group">
            <label for="login">Login:</label>
            <input type="login" class="form-control" name="login" id="login" placeholder="Enter login"
                value="<?= isset($_POST["login"]) ? htmlspecialchars($_POST["login"]) : "" ?>">
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
        </div>
        <button type="submit" class="btn btn-default pull-left">Submit</button>

    </form>

<?php


}
