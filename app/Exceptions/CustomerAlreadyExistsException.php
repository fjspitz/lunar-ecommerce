<?php

namespace App\Exceptions;

use Exception;

class CustomerAlreadyExistsException extends Exception
{
    public function errorMessage()
    {
        $message = 'Customer already exists.';

        return $message;
    }
}
