<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertsFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'All Categories',
                'label' => 'Category',
            ])
            ->add('minPrice', NumberType::class, [
                'required' => false,
                'label' => 'Min Price (£)',
                'attr' => ['placeholder' => 'Min'],
            ])
            ->add('maxPrice', NumberType::class, [
                'required' => false,
                'label' => 'Max Price (£)',
                'attr' => ['placeholder' => 'Max'],
            ])
            ->add('location', TextType::class, [
                'required' => false,
                'label' => 'Location',
                'attr' => ['placeholder' => 'Enter location'],
            ])
            ->add('onlyMyAdverts', CheckboxType::class, [
                'label' => 'Show only my adverts',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
