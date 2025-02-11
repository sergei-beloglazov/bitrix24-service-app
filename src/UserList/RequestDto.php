<?php

namespace Bitrix24Integration\UserList;

/**
 * A Data Transfer Object for the ServiceApp user list request.
 */
class RequestDto
{
    /** @var int This parameter is used to manage pagination. */
    public int $start = 0;

    /**
     * Get the start parameter for pagination.
     *
     * @return int
     */
    public function getStart(): int
    {
      return $this->start;
    }

    /**
     * Set the start parameter for pagination.
     *
     * @param int $start
     */
    public function setStart(int $start): void
    {
      $this->start = $start;
    }
  }
