<?php

namespace App\Exceptions;

use Exception;

class CustomerGroupAlreadyExistsException extends Exception
{
    public function errorMessage()
    {
        $message = 'Customer group already exists.';

        return $message;
    }
}
