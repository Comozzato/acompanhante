<?php

namespace App\Exceptions;

use Exception;

abstract class ApiException extends Exception
{
    protected $statusCode = 500;
    protected $errorCode;
    protected $details = [];

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode ?? strtoupper(str_replace(' ', '_', $this->getMessage()));
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails(array $details)
    {
        $this->details = $details;
        return $this;
    }
}