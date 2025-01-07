<?php

namespace App\Controller;

use App\Entity\Adverts;
use App\Form\AdvertCreateEditFormType;
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
        $this->denyAccessUnlessGranted('ROLE_USER');

        $advert = new Adverts();
        $form = $this->createForm(AdvertCreateEditFormType::class, $advert, [
            'isEdit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()&& $form->isValid()) {
                $advert->setUser($this->getUser());
                $entityManager->persist($advert);
                $entityManager->flush();

                $this->addFlash('success', 'Advert created successfully.');
                return $this->redirectToRoute('app_adverts_list');
        }
        else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('adverts/advert_create_edit.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/adverts', name: 'app_adverts_list')]
    public function list(AdvertRepository $advertRepository, Request $request): Response
    {
        $form = $this->createForm(AdvertsFilterFormType::class, null, [
            'method' => 'get',//csrf not needed as this is basically a read only operation
        ]);

        $form->handleRequest($request);

        $filters = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }

        if (!empty($filters['onlyMyAdverts']) && $this->getUser()) {
            $filters['user'] = $this->getUser();
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $paginator = $advertRepository->getPaginatedAdverts($page, $limit, $filters);

        return $this->render('adverts/adverts_list.html.twig', [
            'form' => $form->createView(),
            'adverts' => $paginator,
            'currentPage' => $page,
            'totalPages' => ceil($paginator->count() / $limit),
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

        if (!$this->isCsrfTokenValid('delete' . $advert->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_adverts_list');
        }

        $entityManager->remove($advert);
        $entityManager->flush();

        $this->addFlash('success', 'Advert deleted successfully.');
        return $this->redirectToRoute('app_adverts_list');
    }

    #[Route('/advert/edit/{id}', name: 'app_advert_edit')]
    public function edit(int $id, Request $request, AdvertRepository $advertRepository, EntityManagerInterface $entityManager): Response
    {
        $advert = $advertRepository->find($id);

        if (!$advert) {
            $this->addFlash('error', 'Advert not found.');
            return $this->redirectToRoute('app_adverts_list');
        }

        if ($advert->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You do not have permission to edit this advert.');
            return $this->redirectToRoute('app_adverts_list');
        }

        $form = $this->createForm(AdvertCreateEditFormType::class, $advert, [
            'isEdit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $entityManager->flush();
           $this->addFlash('success', 'Advert updated successfully.');
           return $this->redirectToRoute('app_adverts_list');
        }
        else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('adverts/advert_create_edit.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
    }
}
