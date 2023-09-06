<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidateAttendeeInfoValidator extends ConstraintValidator
{
    public function __construct(private RequestStack $requestStack) { }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidateAttendeeInfo) {
            throw new UnexpectedTypeException($constraint, AccessToAttendeeInfo::class);
        }

        if (empty($value)) {
            return;
        }

        $formRaw = $this->requestStack->getCurrentRequest()->request->all();
        $formData = $formRaw['attendee_info'] ?? null;

        if ($value !== $formData['reference']) {
            $this->context->buildViolation('Error inesperado con la referencia de tu entrada')
                ->addViolation();
        }

    }

}