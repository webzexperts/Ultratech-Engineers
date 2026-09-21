<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public $item;

    public function __construct($message = null, $item = null)
    {
        parent::__construct($message);
        $this->item = $item;
    }
}