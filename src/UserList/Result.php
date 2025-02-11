<?php

namespace Bitrix24Integration\UserList;

/**
 * Operation result
 */
class Result
{

  /** @var bool Success flag */
  public bool  $success = false;

  /** @var string Error text     */
  public string  $errorMessage = "";

  /**
   * Get the value of success
   */
  public function isSuccess()
  {
    return $this->success;
  }

  /**
   * Set the value of success
   * @param bool $success
   * @return self
   */
  public function setSuccess(bool $success)
  {
    $this->success = $success;
    return $this;
  }

  /**
   * Set the value of errorMessage
   *
   * @return self
   */
  public function setErrorMessage($errorMessage)
  {
    $this->errorMessage = $errorMessage;
    return $this;
  }

  /**
   * Clear all
   * @return self
   */
  public function clear(): self
  {
    $this->setSuccess(false)->setErrorMessage("");
    return $this;
  }
}
