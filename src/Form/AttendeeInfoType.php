<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AttendeeInfo;
use App\Validator\AccessToAttendeeInfo;
use App\Validator\ValidateAttendeeInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendeeInfoType extends AbstractType
{
    public function __construct(
        private AccessToAttendeeInfo $accessToAttendeeInfo,
        private ValidateAttendeeInfo $validateAttendeeInfo
    ) { }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ticketReference', TextType::class, [
                'attr' => ['class' => 'form-control form-control-sm'],
                'required' => true,
                'label' => 'Referencia de la entrada',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [$this->accessToAttendeeInfo],
                'mapped' => true,
            ])
            ->add('ticketEmail', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Correo electrónico asociado a la entrada',
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'pb-4'],
                'mapped' => true,
            ])
            ->add('reference', TextType::class, [
                'attr' => ['class' => 'd-none'],
                'required' => true,
                'label' => 'Referencia de la entrada',
                'label_attr' => ['class' => 'form-label d-none'],
                'row_attr' => ['class' => 'pt-4'],
                'constraints' => [$this->validateAttendeeInfo],
            ])
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
            ->add('dni', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'DNI/NIE *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Correo electrónico *',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('city', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Ciudad',
                'required' => false,
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('state', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Comunidad autónoma',
                'required' => false,
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('position', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Posición actual',
                'required' => false,
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('years', ChoiceType::class, [
                'placeholder' => 'Selecciona...',
                'required' => false,
                'choices' => [
                    'menos de 1' => 0,
                    'un año' => 1,
                    'dos años' => 2,
                    'más de tres' => 3,
                    'más de cinco' => 5,
                    'más de diez' => 10,
                    'más de quince' => 15
                ],
                'attr' => ['class' => 'form-select'],
                'label' => 'Años de experiencia',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('workStatus', ChoiceType::class, [
                'placeholder' => 'Selecciona...',
                'required' => false,
                'choices' => [
                    'no quiero cambiar de trabajo' => 'no',
                    'dispuesto a escuchar ofertas' => 'open',
                    'en busca activa de trabajo' => 'active',
                ],
                'attr' => ['class' => 'form-select'],
                'label' => '¿Buscando cambiar de trabajo?',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('workPreference', ChoiceType::class, [
                'placeholder' => 'Selecciona...',
                'required' => false,
                'choices' => [
                    'presencial' => 'presencial',
                    'híbrida' => 'híbrida',
                    'remoto' => 'remoto',
                    'remoto en equipos asíncronos' => 'full remote'
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Modalidad de trabajo preferida',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'w-100 btn btn-primary btn-sm mt-2'],
                'label' => 'Actualizar'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AttendeeInfo::class
        ]);
    }
}
