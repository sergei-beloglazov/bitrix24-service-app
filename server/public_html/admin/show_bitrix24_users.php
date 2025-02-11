<?php
require_once("auth.php");
//Check authorization
if (!auth()) {
    die();
}


require $_SERVER['DOCUMENT_ROOT'] . "/includes.php";
//Show Bitrix24 CRM users
require $PATH_TO_BITRIX24_INTEGRATION_LIB . "Admin/ShowBitrix24Users.php";
