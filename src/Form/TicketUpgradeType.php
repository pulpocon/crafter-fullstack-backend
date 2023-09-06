<?php

declare(strict_types=1);

namespace App\Form;

use App\Validator\UpgradeTicket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketUpgradeType extends AbstractType
{
    public function __construct(private UpgradeTicket $upgradeTicket) { }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'attr' => ['class' => 'form-control form-control-sm'],
                'required' => true,
                'label' => '¿Tienes ya entrada y quieres actualizarla? Escribe aquí la referencia de entrada que deseas actualizar',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [$this->upgradeTicket]
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'w-100 btn btn-danger btn-sm mt-2'],
                'label' => 'Actualizar entrada'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}