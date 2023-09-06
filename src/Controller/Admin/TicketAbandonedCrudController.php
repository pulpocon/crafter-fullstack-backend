<?php

namespace App\Controller\Admin;

use App\Entity\TicketAbandoned;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TicketAbandonedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TicketAbandoned::class;
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
}
