<?php

namespace Bitrix24Integration\App;

use Bitrix24Integration\Lib\CRest;

?><html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="css/app.css">
    <!--link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"-->
    <title>ServiceApp</title>
    <style>
        body {
            color: rgb(83, 92, 105);
        }



        .button-save {
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <form id="service-app-settings">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">
                    <h2>ServiceApp installation</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12 col-md-7 col-lg-9">
                    <?php
                    //Install result
                    $result = ["install" => false];
                    //Check POST data
                    if (isset($_REQUEST["save"]) && $_REQUEST["save"] == "Y") {
                        $result = CRest::runInstallApp();
                        //Check install success
                        if (!$result["install"]) {
                            echo "<b>An error has occured: </b> <span style='color:red'>" . $result["error"] . "</span><br><br>";
                        }
                    }
                    //Check install success
                    if (!$result["install"]) {
                    ?>
                        <input type="hidden" name="save" value="Y">
                        <input type="hidden" name="access_token" value="<?= htmlspecialchars($_REQUEST['AUTH_ID'] ?? $_REQUEST["access_token"]); ?>">
                        <input type="hidden" name="expires_in" value="<?= htmlspecialchars($_REQUEST['AUTH_EXPIRES'] ?? $_REQUEST["expires_in"]); ?>">
                        <input type="hidden" name="refresh_token" value="<?= htmlspecialchars($_REQUEST['REFRESH_ID'] ?? $_REQUEST["refresh_token"]); ?>">
                        <input type="hidden" name="application_token" value="<?= htmlspecialchars($_REQUEST['APP_SID'] ?? $_REQUEST["application_token"]); ?>">
                        <input type="hidden" name="domain" value="<?= htmlspecialchars($_REQUEST['DOMAIN'] ?? $_REQUEST["domain"]); ?>">
                        <input type="hidden" name="member_id" value="<?= htmlspecialchars($_REQUEST['MEMBER_ID'] ?? $_REQUEST['member_id']); ?>">
                        <div class="form-group">
                            <label for="service_app_token">ServiceApp token:</label>
                            <input type="text" class="form-control" name="service_app_token" id="service_app_token"
                                placeholder="Please enter your ServiceApp token"
                                value="<?= isset($_REQUEST['service_app_token']) ? htmlspecialchars($_REQUEST['service_app_token']) : "" ?>">
                        </div>
                </div>
            </div>
            <div class="row my-3">
                <div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">

                    <button type="submit" class="btn btn-primary btn-lg btn-save">Save</button>
                <?
                    } else { // /Check install success
                ?>
                    <b>Thank you! The application was successfully installed.</b>

                    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
                    <!-- Include all compiled plugins (below), or include individual files as needed -->
                    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
                    <script src="//api.bitrix24.com/api/v1/dev/"></script>

                    <script>
                        BX24.init(function() {
                            var size = BX24.getScrollSize();
                            BX24.resizeWindow(size.scrollWidth, 600);
                            BX24.fitWindow();
                            BX24.installFinish();
                        });
                    </script>
                <?
                    } // /Check install success
                ?>
                </div>
            </div>

        </div>
    </form>

</body>

</html>