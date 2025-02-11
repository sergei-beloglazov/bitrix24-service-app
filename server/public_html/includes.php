<?php
//DEBUG
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

//Composer autoloader
require($_SERVER['DOCUMENT_ROOT'] . "/../vendor/autoload.php");
/** @var string Path to Bitrix24 integration library */
$PATH_TO_BITRIX24_INTEGRATION_LIB = $_SERVER['DOCUMENT_ROOT'] . "/../vendor/sergei-beloglazov/bitrix24-service-app/src/";
