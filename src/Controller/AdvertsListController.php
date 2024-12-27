<?php

namespace App\Controller;

use App\Entity\Adverts;
use App\Form\AdvertsFilterFormType;
use App\Repository\AdvertRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdvertsListController extends AbstractController
{
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
        return $this->render('adverts_list/index.html.twig', [
            'form' => $form->createView(),
            'adverts' => $paginator,
            'currentPage' => $page,
            'totalPages' => ceil($paginator->count() / $limit),// clean way for rounding page numbers :)
        ]);
    }
}
