<?php

namespace App\Form;

use App\Entity\Hotel;
use App\Entity\User;
use Doctrine\DBAL\Types\StringType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SendRequestCustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('IBAN')
            ->add('name', TextType::class)
            ->add('adresse', TextType::class, [
                'mapped' => false,
            ])
            ->add('ville', TextType::class, [
                'mapped' => false,
            ])
            ->add('code_postal', TextType::class, [
                'mapped' => false,
            ])
            ->add('telephone', TextType::class, [
                'mapped' => false,
            ])
            ->add('description', TextType::class, [
                'mapped' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image mise en avant',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                         'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image type (jpeg ou jpg)',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class,
        ]);
    }
}
