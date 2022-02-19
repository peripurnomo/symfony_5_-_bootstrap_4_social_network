<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

/**
 * Defines the custom form field type used to change user's password.
 */
class ResetPasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 8,
                        'max' => 128,
                    ]),
                ],

                'first_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'New password',
                        'autocomplete' => 'off'
                    ]
                ],
                
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Repeat password',
                        'autocomplete' => 'off'
                    ]
                ],
            ])
        ;
    }
}