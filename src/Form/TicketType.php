<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Ticket;
use App\Validator\UpgradeTicket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class TicketType extends AbstractType
{
    public function __construct(private UpgradeTicket $upgradeTicket)
    { }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Nombre *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('surname', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Apellidos *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Correo electrónico del asistente *',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => $this->getEmailConstraints($builder)
            ])
            ->add('emailInvoice', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Correo electrónico para facturación *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('shirtType', ChoiceType::class, [
                'placeholder' => 'Selecciona...',
                'choices' => [
                    'Unisex' => 'hombre',
                    'Mujer' => 'mujer'
                ],
                'attr' => ['class' => 'form-select'],
                'required' => true,
                'label' => 'Tipo de camiseta *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('shirtSize', ChoiceType::class, [
                'placeholder' => 'Selecciona...',
                'choices' => [
                    'XS' => 'xs',
                    'S' => 's',
                    'M' => 'm',
                    'L' => 'l',
                    'XL' => 'xl',
                    'XXL' => 'xxl',
                    'XXXL' => 'xxxl'
                ],
                'attr' => ['class' => 'form-select'],
                'required' => true,
                'label' => 'Talla de la camiseta *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('feeding', ChoiceType::class, [
                'placeholder' => 'Selecciona...',
                'choices' => [
                    'Omnivoro' => 'Omnivoro',
                    'Vegetariano' => 'Vegetariano',
                    'Vegano' => 'Vegano',
                ],
                'attr' => ['class' => 'form-select'],
                'required' => true,
                'label' => 'Tipo de alimentación *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('allergies', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Indique sus intolerancias o alergias si procede',
                'label_attr' => ['class' => 'form-label'],
                'required' => false
            ])
            ->add('ticketConditions', CheckboxType::class, [
                'attr' => ['class' => 'form-check-input'],
                'label' => 'Acepto y entiendo las
                            <a href="/condiciones-entrada" target="_blank"
                                title="condiciones de venta de entradas">condiciones de venta de entradas</a> y la
                            <a href="/politica-privacidad" target="_blank"
                                title="política de privacidad">política de privacidad</a>',
                'label_attr' => ['form-check-label'],
                'label_html' => true,
                'mapped' => false,
                'required' => true
            ])
            ->add('upgradedFrom', EntityType::class, [
                'class' => Ticket::class,
                'choices' => $this->getUpgradedFromChoices($builder),
                'constraints' => [$this->upgradeTicket]
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'w-100 btn btn-primary btn-lg'],
                'label' => 'Proceder al pago'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class
        ]);
    }

    private function getEmailConstraints(FormBuilderInterface $builder) : array
    {
        /** @var Ticket $entity */
        $entity = $builder->getData();
        $ticketPlan = $entity->getTicketPlan();
        if (null === $ticketPlan || true === $ticketPlan->isVisible()) {
            return [];
        }
        return [new Choice([], $ticketPlan->getAllowedEmails())];
    }

    private function getUpgradedFromChoices(FormBuilderInterface $builder) : array
    {
        /** @var Ticket $ticket */
        $ticket = $builder->getData();
        if (false === $ticket->isUpgraded()) {
            return [];
        }

        return [$ticket->getUpgradedFrom()];
    }
}
