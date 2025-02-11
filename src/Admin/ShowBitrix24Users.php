<?php

namespace Bitrix24Integration\App;

use Bitrix24Integration\Lib\CRest;
?><html>

<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <title>ServiceApp admin - show Bitrix24 users</title>
</head>

<body>
    <H1>ServiceApp - show Bitrix24 users</H1>
    <style>
        body {
            margin: 10px;
        }

        option[data-placeholder] {
            display: none;
        }

        .send-table {
            width: auto;
        }

        input {
            width: 340px;
        }
    </style>


    <?php


    //Check if need to send data
    if (!empty($_POST["showUsers"]) && $_POST["showUsers"] == "y") {
    ?>
        <br>
        <form method="POST">
            <button type="submit" class="btn btn-md btn-success">Send a new request</button>
        </form><br>
    <?

        $sendResult = CRest::runGetBitrix24Users();
        if (!$sendResult["send"]) {
            echo "<b>An error has occured: </b> <span style='color:red'>" . $sendResult["error"] . "</span><br><br>";
        } else {
            //DEBUG
            echo '<b>Bitrix24 getting user list result:</b><pre>' . var_export($sendResult, true) . '</pre>';
        }
    }


    //Get client list
    $clientList = CRest::runGetClientsList();

    ?>
    <form method="POST">
        <table id="send-table" class="send-table table table-striped" style="max-width: 1300px;">
            <tr>
                <td>Client:</td>
                <td>
                    <select id="client_id" name="CLIENT_ID" >
                        <option value="" data-placeholder>- Select a client -</option>
                        <?php
                        //Loop through clients
                        foreach ($clientList["loadData"] as $client) {
                            //Installed flag
                            $installed = (intval($client["installed"]) == 1);
                            //Client ID
                            $clientId = intval($client["id"]);
                        ?>
                            <option value="<?= intval($client["id"]) ?>"
                                <?= $installed ? "" : "disabled" ?>
                                <?= isset($_POST["CLIENT_ID"]) && (intval($_POST["CLIENT_ID"]) == $clientId) ? "selected" : "" ?>>
                                <?= htmlspecialchars($client["name"]) ?>
                                <?= $installed ? "" : " (not installed)" ?>
                            </option>
                        <?php
                        } // /Loop through clients
                        ?>

                    </select>
                </td>
            </tr>
        </table>
        <input type="hidden" name="showUsers" value="y">
        <button type="submit" class="btn btn-primary btn-lg btn-save button-save">Show Bitrix24 users</button>

    </form>
    <hr>

</body>

</html>