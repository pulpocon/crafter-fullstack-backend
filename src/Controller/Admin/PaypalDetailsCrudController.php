<?php

namespace App\Controller\Admin;

use App\Entity\PaypalDetails;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PaypalDetailsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PaypalDetails::class;
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
