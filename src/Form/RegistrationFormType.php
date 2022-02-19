<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', TextType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Nama lengkap',
                    'autofocus'   => 1
                ]
            ])
            ->add('username', TextType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Nama pengguna'
                ]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => false,
                    'attr'  => [
                        'placeholder' => 'E-mail aktif'
                    ]
                ],
                'second_options' => [
                    'label' => false,
                    'attr'  => [
                        'placeholder' => 'Ulangi e-mail'
                    ]
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Kata sandi baru'
                ]
            ])
            ->add('birthdate', BirthdayType::class, [
                'label'       => 'Tanggal Lahir :',
                'placeholder' => [
                    'day'   => 'Tanggal',
                    'month' => 'Bulan',
                    'year'  => 'Tahun'
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'label'   => false,
                'choices' => [
                    'Pria'    => 'Pria',
                    'Wanita'  => "Wanita",
                    'Lainnya' => "Lainnya"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}