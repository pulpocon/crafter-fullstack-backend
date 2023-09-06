<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Service\InvoiceDocumentGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceCrudController extends AbstractCrudController
{
    public function __construct(
        private InvoiceDocumentGenerator $invoiceDocumentGenerator,
        private InvoiceRepository $invoiceRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return Invoice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'ASC'])->setPaginatorPageSize(100);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('downloadInvoice', 'Download invoice')
                ->linkToRoute('download_invoice', static function (Invoice $invoice) : array {
                    return ['id' => $invoice->getId()];
                })
        );
        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('email')
            ->add('cif');
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                'id',
                'cif',
                'businessName',
                'email'
            ];
        }

        return parent::configureFields($pageName);
    }

    #[Route('/admin/invoice/{id}/download', name: 'download_invoice')]
    public function downloadInvoice(int $id): Response
    {
        $invoice = $this->invoiceRepository->find($id);

        $response = new Response($this->invoiceDocumentGenerator->pdf($invoice));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'invoice-' . $invoice->getId() . '.pdf'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
