<?php

namespace Bitrix24Integration\App;

use Bitrix24Integration\Lib\CRest;

?><html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ServiceApp admin - clients</title>
</head>

<body style="padding:5px;">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            margin: 10px;
        }
    </style>
    <h3>ServiceApp application client list</h3>
    <?php
    //Get client list
    $clientList = CRest::runGetClientsList();

    //Check load error
    if (!$clientList["load"]) {
        echo "<b>An error has occured: </b> <span style='color:red'>" . $clientList["error"] . "</span><br><br>";
    } else {
    ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Client name</th>
                    <th scope="col">Installed</th>
                    <th scope="col">Client token</th>
                    <th scope="col">Bitrix24 Domain</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //Loop through clients
                foreach ($clientList["loadData"] as $client) {
                ?>
                    <tr>
                        <th scope="row"><?= intval($client["id"]) ?></th>
                        <td><?= htmlspecialchars($client["name"]) ?></td>
                        <td><?= intval($client["installed"]) == 1 ? "<b style='color:green'>YES</b>" : "-" ?></td>
                        <td><?= htmlspecialchars($client["service_app_token"]) ?></td>
                        <td><?= htmlspecialchars($client["domain"]) ?></td>
                    </tr>
                <?php
                } // /Loop through clients
                ?>

            </tbody>
        </table>
    <?
    } // /Check load error
    ?>
</body>

</html>