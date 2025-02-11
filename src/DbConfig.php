<?php

namespace Bitrix24Integration;

class DbConfig
{
    public $host = "";
    public $user = "";
    public $password = "";
    public $db = "";

    /**
     * The constructor loads configuration file from 
     * DOCUMENT_ROOT/../settings/.db_settings.php
     * 
     * A sample: "settings/.db_settings.php"
     */
    public function __construct()
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/../settings/.db_settings.php");
        $this->host = defined("DB_HOST") ? constant("DB_HOST") : "";
        $this->user = defined("DB_USER") ? constant("DB_USER") : "";
        $this->password = defined("DB_PASSWORD") ? constant("DB_PASSWORD") : "";
        $this->db = defined("DB_DB") ? constant("DB_DB") : "";
    }
}
