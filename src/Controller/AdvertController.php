<?php

namespace App\Controller;

use App\Entity\Adverts;
use App\Form\AdvertCreateFormType;
use App\Form\AdvertsFilterFormType;
use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdvertController extends AbstractController
{
    #[Route('/advert/create', name: 'app_advert_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Only allow logged-in users to access this page
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Create a new Advert entity
        $advert = new Adverts();

        // Create the form
        $form = $this->createForm(AdvertCreateFormType::class, $advert);
        $form->handleRequest($request);

        // Process the form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the currently logged-in user as the owner
            $advert->setUser($this->getUser());

            // Save the advert
            $entityManager->persist($advert);
            $entityManager->flush();

            // Add a success flash message
            $this->addFlash('success', 'Advert created successfully.');

            // Redirect to a different page (e.g., advert list or detail view)
            return $this->redirectToRoute('app_adverts_list');
        }

        // Render the form
        return $this->render('adverts/advert_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/adverts', name: 'app_adverts_list')]
    public function list(AdvertRepository $advertRepository, Request $request): Response
    {
        // Create the filter form
        $form = $this->createForm(AdvertsFilterFormType::class, null, [
            'method' => 'get', // Submit the form via GET
        ]);

        $form->handleRequest($request);

        // Initialize filters
        $filters = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }

        // Handle pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Fetch filtered adverts
        $paginator = $advertRepository->getPaginatedAdverts($page, $limit, $filters);

        // Render the template
        return $this->render('adverts/adverts_list.html.twig', [
            'form' => $form->createView(),
            'adverts' => $paginator,
            'currentPage' => $page,
            'totalPages' => ceil($paginator->count() / $limit),// clean way for rounding page numbers :)
        ]);
    }



    #[Route('/advert/delete/{id}', name: 'app_advert_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        AdvertRepository $advertRepository
    ): Response {
        $advert = $advertRepository->find($id);

        if (!$advert) {
            $this->addFlash('error', 'Advert not found.');
            return $this->redirectToRoute('app_adverts_list');
        }

        // Validate CSRF token
        if (!$this->isCsrfTokenValid('delete' . $advert->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_adverts_list');
        }

        // Remove the advert
        $entityManager->remove($advert);
        $entityManager->flush();

        $this->addFlash('success', 'Advert deleted successfully.');
        return $this->redirectToRoute('app_adverts_list');
    }
}
