<?php
namespace App\Controller;

use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Retrieve login errors, if any
        $error = $authenticationUtils->getLastAuthenticationError();

//        // Get the last username entered by the user
//        $lastUsername = $authenticationUtils->getLastUsername();

        // Create the login form with pre-filled email
        $form = $this->createForm(LoginFormType::class, null, [
            'validation_groups' => ['login'], // Explicitly pass the login group
        ]);

        return $this->render('login/login.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        //This method can be blank - it will be intercepted by the logout key on firewall
        throw new \LogicException('');
    }
}
