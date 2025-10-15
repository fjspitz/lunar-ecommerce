<?php

namespace App\Exceptions;

use Exception;

class MissingCustomerGroupException extends Exception
{
    public function errorMessage()
    {
        $message = 'Customer needs a customer group (company). This seems like a configuration issue.';

        return $message;
    }
}
