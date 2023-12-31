<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use App\Service\SendTicketEmail;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TicketCrudController extends AbstractCrudController
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private RequestStack $requestStack,
        private AdminUrlGenerator $adminUrlGenerator,
        private SendTicketEmail $sendTicketEmail
    ) {}

    public static function getEntityFqcn(): string
    {
        return Ticket::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'ASC'])->setPaginatorPageSize(100);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions); // TODO: Change the autogenerated stub
        $actions->add(Crud::PAGE_INDEX, Action::new('resendMail', 'Resend email')
            ->linkToRoute('resend_email', static function (Ticket $ticket) : array {
                return ['id' => $ticket->getId()];
            })->displayIf(static function (Ticket $ticket) {
                return $ticket->isFinished();
            })
        );
        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('ticketPlan')
            ->add('feeding')
            ->add('revoked');
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                'id',
                'reference',
                'revoked',
                'ticketPlanName',
                'name',
                'surname',
                'email',
                'startDate',
                'endDate'
            ];
        }

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            return [
                AssociationField::new('ticketPlan', 'Ticket Plan'),
                'name',
                'surname',
                EmailField::new('email', 'Email'),
                EmailField::new('emailInvoice', 'Email facturación'),
                ChoiceField::new('shirtType')->setChoices(array_combine(Ticket::SHIRT_TYPES, Ticket::SHIRT_TYPES)),
                ChoiceField::new('shirtSize')->setChoices(array_combine(Ticket::SHIRT_SIZES, Ticket::SHIRT_SIZES)),
                ChoiceField::new('feeding')->setChoices(array_combine(Ticket::FEEDINGS, Ticket::FEEDINGS)),
                'allergies',
                'startDate',
                'endDate',
                'revoked'
            ];
        }

        return parent::configureFields($pageName);
    }

    #[Route('/admin/ticket/{id}/resendEmail', name: 'resend_email')]
    public function resendEmail(int $id) : Response
    {
        $ticket = $this->ticketRepository->find($id);

        if (null === $ticket || null === $ticket->getTicketPlan()) {
            $this->requestStack->getSession()->getFlashBag()->add('error', 'Ticket not found');
        } else {
            $this->sendTicketEmail->__invoke($ticket);
            $this->requestStack->getSession()->getFlashBag()->add(
                'success',
                'Email ' . $ticket->getTicketPlan()->getName() . ' resent to ' . $ticket->getEmail()
            );
        }

        $url = $this->adminUrlGenerator
            ->setController(__CLASS__)
            ->setAction(Action::INDEX)
            ->generateUrl();
        return new RedirectResponse($url);
    }
}
