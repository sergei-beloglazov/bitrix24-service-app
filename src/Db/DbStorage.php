<?php

namespace Bitrix24Integration\Db;

use Bitrix24Integration\DbConfig;
use mysqli;

/**
 * Class for DB data mangement
 */
class DbStorage
{
    /** @var mysqli mysqli DB connection*/
    protected $mysqli = null;
    /** @var string Last error text */
    protected $lastError = "";
    /** @var bool Success flag     */
    protected $success = false;
    /**
     * The constructor creates DB manger using mysqli DbConfig configuration
     *
     * @param mysqli $mysqli
     * 
     */
    public function __construct()
    {
        //Settings
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        //Clear error
        $this->clearLastError();
        //Load DB config
        $dbConfig = new DbConfig();
        //Connect to DB
        $this->mysqli = new mysqli(
            $dbConfig->host,
            $dbConfig->user,
            $dbConfig->password,
            $dbConfig->db
        );

        // Check connection
        if ($this->mysqli->connect_errno) {
            $this->lastError = $this->mysqli->connect_error;
            $this->setSuccess(false);
            return;
        }
        $this->setSuccess(true);
    }

    /**
     * Get the value of lastError
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Set the value of lastError
     *
     * @return  self
     */
    public function setLastError($lastError)
    {
        $this->lastError = $lastError;
        return $this;
    }
    /**
     * Clear the value of lastError
     *
     * @return  self
     */
    public function clearLastError()
    {
        $this->lastError = "";
        return $this;
    }

    /**
     * Get the value of success
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * Set the value of success
     *
     * @return  self
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * Get the value of mysqli
     */
    public function getMysqli()
    {
        return $this->mysqli;
    }
}
