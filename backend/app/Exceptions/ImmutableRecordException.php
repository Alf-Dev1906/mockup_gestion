<?php

namespace App\Exceptions;

use Exception;

class ImmutableRecordException extends Exception
{
    public function __construct($message = 'Este registro no puede ser modificado')
    {
        parent::__construct($message);
    }

    public function report()
    {
        // Log the exception if needed
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error' => 'immutable_record'
        ], 422);
    }
}
