<?php

namespace App\Form;

use App\Entity\Adverts;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Count;

class AdvertCreateEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter a title'
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter description',
                ],
            ])
            ->add('price', TextType::class, [
                'label' => 'Price (£)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter price in £',
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter location',
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name', // Assuming Category entity has a "name" property
                'label' => 'Category',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('images', FileType::class, [
                'label' => 'Images (up to 4)',
                'mapped' => false, // Not directly mapped to the entity
                'multiple' => true,
                'required' => false,
                'constraints' => [
                    new Count(['max' => 4, 'maxMessage' => 'You can only upload up to 4 images.']),
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG or PNG).',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['isEdit'] ? 'Update Advert' : 'Create Advert',
                'attr' => ['class' => 'btn'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adverts::class,
            'csrf_protection' => true,
            'isEdit' => false,
            'validation_groups' => function (FormInterface $form) {
                return $form->getConfig()->getOption('isEdit') ? ['edit'] : ['creation'];
            },
        ]);
    }
}
