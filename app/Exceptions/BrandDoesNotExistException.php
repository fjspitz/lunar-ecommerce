<?php

namespace App\Exceptions;

use Exception;

class BrandDoesNotExistException extends Exception
{
    public function errorMessage()
    {
        $message = 'The brand id given does not exist.';

        return $message;
    }
}
