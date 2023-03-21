<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'min' => date('Y-m-d'),
                    'class' => 'focus:outline-none font-bold text-xl bg-transparent',
                    'id' => 'checkIn',
                ]
            ])
            ->add('end', DateType::class,[
                'widget' => 'single_text',
                'attr' => [
                    'min' => date('Y-m-d'),
                    'class' => 'pl-20 focus:outline-none font-bold text-xl bg-transparent',
                    'id' => 'checkOut',
                ]
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Votre Nom !',
                    'class' => 'bg-transparent border border-gray-200 placeholder:italic placeholder:text-slate-400 focus:outline-none shadow p-3 rounded-xl'
                ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
