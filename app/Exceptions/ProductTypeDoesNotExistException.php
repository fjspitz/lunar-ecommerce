<?php

namespace App\Exceptions;

use Exception;

class ProductTypeDoesNotExistException extends Exception
{
    public function errorMessage()
    {
        $message = 'The product type id given does not exist.';

        return $message;
    }
}
