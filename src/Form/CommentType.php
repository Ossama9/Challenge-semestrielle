<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextType::class, [
                'attr' => [
                    'placeholder' => 'Post your Comment here !',
                    'class' => 'border border-gray-200 placeholder:italic placeholder:text-slate-400 focus:outline-none shadow p-4 rounded-xl'
                ]])
            ->add('note', ChoiceType::class, [
                'choices' => [
                    '5 stars' => '5',
                    '4 stars' => '4',
                    '3 stars' => '3',
                    '2 stars' => '2',
                    '1 star' => '1',
                ],
                'choice_attr' => [
                    'class' => 'rate',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Notes:',
                'label_attr' => [
                    'class' => 'rate',
                ],
            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
