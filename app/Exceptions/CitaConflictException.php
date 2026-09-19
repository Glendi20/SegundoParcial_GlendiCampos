<?php

namespace App\Exceptions;

use Exception;

class CitaConflictException extends Exception
{
    public function __construct(string $message = 'El doctor ya tiene una cita en ese horario.')
    {
        parent::__construct($message);
    }
}
