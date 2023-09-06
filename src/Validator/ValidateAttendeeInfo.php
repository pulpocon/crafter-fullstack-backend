<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidateAttendeeInfo extends Constraint
{
    public $message = "Error inesperado con la referencia de tu entrada";
    public $mode = 'strict';
}