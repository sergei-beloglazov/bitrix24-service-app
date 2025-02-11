<?php

namespace Bitrix24Integration\Db;

use mysqli;
use mysqli_result;
use mysqli_sql_exception;

/**
 * Class for Clients DB data mangement 
 */
class Clients
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
    public function __construct($mysqli)
    {
        //Save connection
        $this->mysqli = $mysqli;
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

    /** Data management **/

    /**
     * Searches a client by ID
     *
     * @param string $clientId Client ID
     * @return mysqli_result|bool Client data of false on error
     * 
     */
    public function findClientById(string $clientId)/*: mysqli_result|bool*/
    {
        $this->clearLastError();
        $query = sprintf(
            "SELECT * FROM `Clients` WHERE `id`='%d'",
            intval($clientId)
        );

        try {
            $this->setSuccess(true);
            $result = $this->mysqli->query($query);
            return $result;
        } catch (mysqli_sql_exception $e) {
            $this->setSuccess(false);
            $this->setLastError("MySQL query error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Searches a client by service application token
     *
     * @param string $clientToken Client's service application token
     * @return mysqli_result|bool Client data of false on error
     * 
     */
    public function findClientByToken(string $clientToken)/*: mysqli_result|bool*/
    {
        $this->clearLastError();
        $query = sprintf(
            "SELECT * FROM `Clients` WHERE `service_app_token`='%s'",
            $this->mysqli->real_escape_string($clientToken)
        );

        try {
            $this->setSuccess(true);
            $result = $this->mysqli->query($query);
            return $result;
        } catch (mysqli_sql_exception $e) {
            $this->setSuccess(false);
            $this->setLastError("MySQL query error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Searches a client by Bitrix24 instance memberId
     *
     * @param string $memberId Client's Bitrix24 instance memberId
     * @return mysqli_result|bool Client data of false on error
     * 
     */
    public function findClientByMemberId(string $memberId)/*: mysqli_result|bool*/
    {
        $this->clearLastError();
        $query = sprintf(
            "SELECT * FROM `Clients` WHERE `member_id`='%s'",
            $this->mysqli->real_escape_string($memberId)
        );

        try {
            $this->setSuccess(true);
            $result = $this->mysqli->query($query);
            return $result;
        } catch (mysqli_sql_exception $e) {
            $this->setSuccess(false);
            $this->setLastError("MySQL query error: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Saves client data, marks as installed
     *
     * @param mixed $arData
     * @return mysqli_result|bool Client data of false on error
     * 
     */
    public function saveClient($arData)
    {
        $this->clearLastError();
        $query = sprintf(
            "UPDATE `Clients` SET "
                . "`installed`=1, "
                . "`member_id`='%s', "
                . "`access_token`='%s', "
                . "`expires_in`='%d', "
                . "`application_token`='%s', "
                . "`refresh_token`='%s', "
                . "`domain`='%s', "
                . "`client_endpoint`='%s' "
                . " WHERE `id`=%d ",
            $this->mysqli->real_escape_string($arData["member_id"]),
            $this->mysqli->real_escape_string($arData["access_token"]),
            $this->mysqli->real_escape_string($arData["expires_in"]),
            $this->mysqli->real_escape_string($arData["application_token"]),
            $this->mysqli->real_escape_string($arData["refresh_token"]),
            $this->mysqli->real_escape_string($arData["domain"]),
            $this->mysqli->real_escape_string($arData["client_endpoint"]),
            intval($arData["id"]),
        );

        try {
            $this->setSuccess(true);
            $result = $this->mysqli->query($query);
            return $result;
        } catch (mysqli_sql_exception $e) {
            $this->setSuccess(false);
            $this->setLastError("MySQL query error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Saves client settings
     *
     * @param mixed $arData
     * @return mysqli_result|bool Client data of false on error
     * 
     */
    public function saveClientTokens($arData)
    {
        $this->clearLastError();
        $query = sprintf(
            "UPDATE `Clients` SET "
                . "`access_token`='%s', "
                . "`refresh_token`='%s' "
                . " WHERE `id`=%d ",
                $this->mysqli->real_escape_string($arData["access_token"]),
                $this->mysqli->real_escape_string($arData["refresh_token"]),
                $this->mysqli->real_escape_string($arData["id"]),
        );

        try {
            $this->setSuccess(true);
            $result = $this->mysqli->query($query);
            return $result;
        } catch (mysqli_sql_exception $e) {
            $this->setSuccess(false);
            $this->setLastError("MySQL query error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get client list
     *
     * @return mysqli_result|bool Client data of false on error
     * 
     */
    public function getClientsList()/*: mysqli_result|bool*/
    {
        $this->clearLastError();
        $query = sprintf(
            "SELECT * FROM `Clients` "
        );

        try {
            $this->setSuccess(true);
            $result = $this->mysqli->query($query);
            return $result;
        } catch (mysqli_sql_exception $e) {
            $this->setSuccess(false);
            $this->setLastError("MySQL query error: " . $e->getMessage());
            return false;
        }
    }
}
