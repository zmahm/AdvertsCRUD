<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
            ])
            ->add('_csrf_token', HiddenType::class, [
                'mapped' => false, // Not mapped to the entity
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['login'], // Use the login validation group
            'csrf_protection' => true, // Enable CSRF protection
            'csrf_field_name' => '_csrf_token', // Name of the hidden CSRF field
            'csrf_token_id' => 'authenticate', // Match this with the `csrf_token_id` in `security.yaml`
        ]);
    }

    public function getBlockPrefix(): string
    {
        return ''; // Remove the prefix to avoid `login_form[_csrf_token]`
    }
}
