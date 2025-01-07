<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Create a new User entity
        $user = new User();

        // Create and handle the form
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // If form is submitted and valid, process the registration
        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the plain password and set it in the User entity
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPlainPassword() // Use the plainPassword directly from the entity
            );
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']); // Assign the default role

            // Erase credentials because plain password no longer needed
            $user->eraseCredentials();

            // Persist and flush the new user to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Add a success flash message
            $this->addFlash('success', 'Registration successful. You can now log in.');

            // Redirect to the login page
            return $this->redirectToRoute('app_login');
        }
        else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        // Render the registration form
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),

        ]);
    }
}
