<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Ticket;
use App\Repository\TicketPlanRepository;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UpgradeTicketValidator extends ConstraintValidator
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private TicketPlanRepository $ticketPlanRepository,
        private RequestStack $requestStack) { }

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof UpgradeTicket) {
            throw new UnexpectedTypeException($constraint, UpgradeTicket::class);
        }

        if (empty($value)) {
            return;
        }

        $ticket = $value instanceof Ticket ? $value : $this->ticketRepository->findOneBy(['reference' => $value]);

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

        $slug = $this->requestStack->getCurrentRequest()->attributes->get('slug');
        $ticketPlan = $this->ticketPlanRepository->findOneBy(['slug' => $slug]);
        if (null === $ticketPlan) {
            $this->context->buildViolation('Error inesperado')
                ->addViolation();
            return;
        }

        if (in_array('cqrs', $ticketPlan->getAccessTo(), true) || in_array('cqrs-viernes', $ticketPlan->getAccessTo(), true)) {
            $this->context->buildViolation('El taller CQRS tiene que adquirirse de forma independiente, no permite actualización')
                ->addViolation();
            return;
        }

        if ($ticket->getTicketPlan()->getPrice() > $ticketPlan->getPrice()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }

}