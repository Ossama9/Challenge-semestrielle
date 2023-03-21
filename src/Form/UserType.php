<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastname', TextType::class, [
                'label' => 'Nom*',
                'attr' => [
                    'placeholder' => 'Enter your lastname',
                    'autocomplete' => 'family-name',
                    'class' => 'border border-gray-200 placeholder:italic placeholder:text-slate-400 focus:outline-none shadow p-4 rounded-xl'
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom*',
                'attr' => [
                    'placeholder' => 'Enter your firstname',
                    'autocomplete' => 'given-name',
                    'class' => 'border border-gray-200 placeholder:italic placeholder:text-slate-400 focus:outline-none shadow p-4 rounded-xl'
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email*',
                'attr' => [
                    'placeholder' => 'Enter your email',
                    'autocomplete' => 'email',
                    'class' => 'border border-gray-200 placeholder:italic placeholder:text-slate-400 focus:outline-none shadow p-4 rounded-xl'
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('password', RepeatedType::class, [
                'label' => 'Password*',
                'attr' => [
                    'placeholder' => 'Enter your Password',
                    'autocomplete' => 'current-password',
                    'class' => 'border border-gray-200 placeholder:italic placeholder:text-slate-400 focus:outline-none shadow p-4 rounded-xl'
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('_csrf_token', HiddenType::class, [
                'mapped' => false,
                'data' => '{{ csrf_token("authenticate") }}'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Get started',
                'attr' => [
                    'class' => 'bg-indigo-600 rounded-xl py-3 text-xl font-medium text-white shadow'
                ]
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}