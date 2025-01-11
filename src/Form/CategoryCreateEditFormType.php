<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryCreateEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Category Name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter category description',
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'Category Description',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter category description',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['isEdit'] ? 'Update Category' : 'Create Category',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    //isEdit can be modified by controller
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'csrf_protection' => true,
            'isEdit' => false,
            'validation_groups' => function (FormInterface $form) {
                return $form->getConfig()->getOption('isEdit') ? ['edit'] : ['creation'];
            },
        ]);
    }
}
