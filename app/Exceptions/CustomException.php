<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    public $code;
    public $message;

    public function __construct($code , $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function render($request)
    {       
        return response()->json(["error" => true, "message" =>  $this->message ],$this->code );       
    }
}
