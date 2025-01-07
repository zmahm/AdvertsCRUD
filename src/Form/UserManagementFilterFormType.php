<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserManagementFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'Email',
                'attr' => ['placeholder' => 'Search by email'],
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Name',
                'attr' => ['placeholder' => 'Search by name'],
            ])
            ->add('role', ChoiceType::class, [
                'required' => false,
                'label' => 'Role',
                'placeholder' => 'All Roles',
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Moderator' => 'ROLE_MODERATOR',
                    'Admin' => 'ROLE_ADMIN',
                ],
            ])
            ->add('applyFilters', SubmitType::class, [
                'label' => 'Apply Filters',
                'attr' => [
                    'class' => 'filter-button',
                ],
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
