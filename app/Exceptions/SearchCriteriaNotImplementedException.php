<?php

namespace App\Exceptions;

use Exception;

class SearchCriteriaNotImplementedException extends Exception
{
    public function message()
    {
        return 'The search criteria selected is not implemented yet.';
    }
}
