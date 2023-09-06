<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use App\Validator\AccessToAttendeeInfo;
use App\Validator\AccessToAttendeeInvoice;
use App\Validator\UpgradeTicket;
use App\Validator\ValidateAttendeeInfo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    public function __construct(
        private AccessToAttendeeInvoice $accessToAttendeeInvoice,
        private TicketRepository        $ticketRepository
    )
    { }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accessReference', TextType::class, [
                'attr' => ['class' => 'form-control form-control-sm'],
                'required' => true,
                'label' => 'Referencia de la entrada',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [$this->accessToAttendeeInvoice],
                'mapped' => true,
            ])
            ->add('accessEmail', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Correo electrónico para facturación de la entrada',
                'label_attr' => ['class' => 'form-label'],
                'mapped' => true,
            ])
            ->add('businessName', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Nombre de la empresa *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('cif', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'CIF/NIF',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('address', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Dirección fiscal completa',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Correo electrónico para facturación *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('tickets', EntityType::class, [
                'class' => Ticket::class,
                'placeholder' => 'Selecciona...',
                'choices' => $this->getTickets($builder),
                'attr' => ['class' => 'form-select'],
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Entradas a incluir en la factura *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'w-100 btn btn-primary btn-lg'],
                'label' => 'Solicitar factura'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class
        ]);
    }

    private function getTickets(FormBuilderInterface $builder): array
    {
        /** @var Invoice $invoice */
        $invoice = $builder->getData();
        return $this->ticketRepository->ofEmailInvoice($invoice->getEmail());
    }
}
