<?php

namespace App\Controller\Admin;

use App\Entity\TicketPlan;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TicketPlanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TicketPlan::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                'id',
                'name',
                'description',
                'quantity',
                'ticketsSold',
                'availableTickets',
                'price'
            ];
        }

        if ($pageName === Crud::PAGE_DETAIL) {
             return parent::configureFields($pageName);
        }

        $formFields = [
            TextField::new('name'),
            TextField::new('slug'),
            TextField::new('description'),
            ChoiceField::new('accessTo')
                ->allowMultipleChoices()
                ->setChoices(TicketPlan::AVAILABLE_ACCESSES),
            IntegerField::new('position'),
            BooleanField::new('free'),
            NumberField::new('price'),
            NumberField::new('tax'),
            IntegerField::new('quantity'),
            IntegerField::new('fewQuantityAlert'),
            BooleanField::new('active'),
            BooleanField::new('visible'),
            ArrayField::new('allowedEmails')
        ];

        return $formFields;
    }
}
