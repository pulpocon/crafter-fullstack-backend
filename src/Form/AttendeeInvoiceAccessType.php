<?php

declare(strict_types=1);

namespace App\Form;

use App\Validator\AccessToAttendeeInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendeeInvoiceAccessType extends AbstractType
{
    public function __construct(private AccessToAttendeeInvoice $accessToAttendeeInfo)
    { }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'attr' => ['class' => 'form-control form-control-sm'],
                'required' => true,
                'label' => 'Referencia de la entrada',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [$this->accessToAttendeeInfo]
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Correo electrónico para facturación de la entrada',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'w-100 btn btn-primary btn-sm mt-2'],
                'label' => 'Acceder'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}
