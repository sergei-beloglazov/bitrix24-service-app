<?php
require_once("auth.php");
//Check authorization
if (!auth()) {
    die();
}


require $_SERVER['DOCUMENT_ROOT'] . "/includes.php";
//Show clients
require $PATH_TO_BITRIX24_INTEGRATION_LIB . "Admin/ClientsList.php";
