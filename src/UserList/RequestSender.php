<?php

namespace Bitrix24Integration\UserList;

use Bitrix24Integration\Lib\CRest;


/**
 * Sends call data from ServiceApp to CRM Bitrix24
 */
class RequestSender
{
  /** @var RequestDto Call data */
  protected /* RequestDto  */ $requestDto;
  /** @var string Current API function name   */
  protected $apiFunction;
  /** @var array API call input data  */
  protected $apiData = [];
  /** @var object API result data   */
  protected $resultData = null;
  /** @var Result Last operation result*/
  protected Result $result;

  /* Request parameters */

  /** @var int parameter is used to manage pagination. */
  protected int $start = 0;

  /**
   * Constructor
   *
   * @param RequestDto $requestDto Call data
   * 
   */
  public function  __construct(RequestDto $requestDto)
  {
    //Save input data
    $this->requestDto = $requestDto;
    //Initialize the result
    $this->result = new Result();
  }
  /**
   * Sends API request.
   * Clears the result object before send
   * @return self
   * 
   */
  protected function sendRequest(): self
  {
    //Clear the result
    $this->clearResult();

    // Save response, converting to object
    $this->resultData =  json_decode(json_encode(CRest::call(
      $this->apiFunction,
      $this->apiData
    )), false);
    //Log results
    $this->logApiResult();
    return $this;
  }

  /**
   * Get users via user.get API function
   * @return self
   */
  protected function getUsers()
  {
    $this->apiFunction = "user.get";
    //Prepare data
    $this->apiData = [
      "start" => $this->start,
    ];
    //Send the API request
    $this->sendRequest();
    //Check result errors 
    if ($this->hasApiErrors()) {
      return $this;
    }
    //The API request was successfull
    $this->result->setSuccess(true);
    return $this;
  }


  /**
   * Sends call data to Bitrix24
   *
   * @return Result Send result
   * 
   */
  public function sendUserListRequest(): Result
  {
    //Send user.get API request
    $this->getUsers();
    //Check success
    if (!$this->isSuccess()) {
      return $this->result;
    }

    //Success
    return $this->result;
  }

  /**
   * Checks if API result has errors. Set result object to error.
   * 
   * @return bool true, if API result has errors
   */
  protected function hasApiErrors(): bool
  {
    if (!empty($this->resultData->error)) {
      //Set error flag
      $this->result->setSuccess(false)
        //Set error description from API data
        ->setErrorMessage("An API call to the function '" . $this->apiFunction . "' "
          . " has returned an error '" . $this->resultData->error . "': "
          . ($this->resultData->error_description ?? " (no error description)"));
      return true;
    }
    //No errors
    return false;
  }

  /**
   * Clears the last operation result. Sets success to **false**
   * @return self
   */
  protected function clearResult(): self
  {
    //Check if the result field was initiated
    if (!isset($this->result)) {
      return $this;
    }
    $this->result->clear();
    return $this;
  }

  /**
   * Check if last operation was successfull
   * @return bool true, if it was
   */
  protected function isSuccess(): bool
  {
    //Check if the result field was initiated
    if (!isset($this->result)) {
      return false;
    }
    return $this->result->isSuccess();
  }
  /**
   * Log a text to the console
   *
   * @param mixed $msg Message
   * @return self
   * 
   */
  protected function log($msg): self
  {
    echo $msg;
    return $this;
  }
  /**
   * Log a variable
   *
   * @param mixed $var A variable
   * @param string $title Title
   * @return self
   * 
   */
  protected function logVar($var, $title = ""): self
  {
    $this->log('<b>' . $title . '</b>' . '<pre>' . var_export($var, true) . '</pre>');
    return $this;
  }


  protected function logApiResult(): self
  {
    //Log API function request results
    $this->logVar($this->resultData, $this->apiFunction . " result:");
    return $this;
  }
  /**
   * Get the start parameter
   * 
   * @return int
   */
  public function getStart(): int
  {
    return $this->start;
  }

  /**
   * Set the start parameter
   * 
   * @param int $start
   * @return self
   */
  public function setStart(int $start): self
  {
    $this->start = $start;
    return $this;
  }
}
