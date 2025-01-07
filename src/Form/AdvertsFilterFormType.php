<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;


class AdvertsFilterFormType extends AbstractType
{
    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
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
            ]);

        // Check if the user is logged in
        if ($this->security->getUser()) {//twig template
            $builder->add('onlyMyAdverts', CheckboxType::class, [
                'label' => 'Show only my adverts',
                'required' => false,
                'row_attr' => [
                    'class' => 'custom-checkbox'
                ]
            ]);
        }
        //avoids manually listing all form rows in twig template
        $builder->add('applyFilters', SubmitType::class, [
            'label' => 'Apply Filters',
            'attr' => [
                'class' => 'filter-button',
            ],
        ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,//this is done as information for this route is read only and publicly accessible
        ]);
    }
}
