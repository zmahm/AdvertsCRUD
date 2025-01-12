<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserManagementFilterFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


class UserManagementController extends AbstractController
{
    /**
     * Displays the user management page with a list of all users.
     */
    #[Route('/admin/users', name: 'admin_user_management')]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(UserManagementFilterFormType::class, null, [
            'method' => 'GET',
        ]);

        $form->handleRequest($request);

        // Ensure filters are initialized as an array
        $filters = $form->isSubmitted() && $form->isValid() ? $form->getData() : [];

        // Pagination parameters
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Fetch paginated users
        $paginator = $userRepository->getPaginatedUsers($page, $limit, $filters);

        return $this->render('user_management/users_list.html.twig', [
            'users' => $paginator,
            'currentPage' => $page,
            'totalPages' => ceil($paginator->count() / $limit),
            'filters' => $filters,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/admin/users/update-role/{id}', name: 'admin_user_update_role', methods: ['POST'])]
    public function updateRole(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $newRole = $request->request->get('role');
        //sadly there is no way to just get a list of all available roles that is clean
        $availableRoles = ['ROLE_ADMIN','ROLE_USER','ROLE_MODERATOR'];

        $submittedToken = $request->request->get('_token');

        // Check if the token is valid
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete' . $user->getId(), "$submittedToken"))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        if (!in_array($newRole, $availableRoles)) {
            $this->addFlash('danger', 'Invalid role selected.');
            return $this->redirectToRoute('admin_user_management');
        }

        $user->setRoles([$newRole]);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'User role updated successfully.');
        return $this->redirectToRoute('admin_user_management');

    }
}
