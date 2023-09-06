<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UpgradeTicket extends Constraint
{
    public $message = "Sólo se permite actualizar tu entrada por otra de mayor importe";
    public $mode = 'strict';
}