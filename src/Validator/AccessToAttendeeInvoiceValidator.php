<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AccessToAttendeeInvoiceValidator extends ConstraintValidator
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private RequestStack $requestStack
    )
    { }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AccessToAttendeeInvoice) {
            throw new UnexpectedTypeException($constraint, AccessToAttendeeInvoice::class);
        }

        if (empty($value)) {
            return;
        }

        $ticket = $this->ticketRepository->findOneBy(['reference' => $value]);

        if (null === $ticket || null === $ticket->getTicketPlan()) {
            $this->context->buildViolation('La referencia indicada no existe')
                ->addViolation();
            return;
        }

        if ($ticket->isRevoked()) {
            $this->context->buildViolation('La referencia indicada ha sido revocada')
                ->addViolation();
            return;
        }

        $formRaw = $this->requestStack->getCurrentRequest()->request->all();
        $formData = $formRaw['attendee_invoice_access'] ?? $formRaw['invoice'] ?? null;
        if ($ticket->getEmailInvoice() !== $formData['email']) {
            $this->context->buildViolation('Dirección de correo de facturación no válida')
                ->addViolation();
        }

    }

}
