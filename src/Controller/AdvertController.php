<?php

namespace App\Controller;

use App\Entity\Adverts;
use App\Entity\AdvertImage;
use App\Form\AdvertCreateEditFormType;
use App\Form\AdvertsFilterFormType;
use App\Repository\AdvertRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AdvertController extends AbstractController
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }


    #[Route('/adverts', name: 'app_adverts_list')]
    public function list(AdvertRepository $advertRepository, Request $request): Response
    {
        $form = $this->createForm(AdvertsFilterFormType::class, null, [
            'method' => 'get',
        ]);
        $form->handleRequest($request);

        $filters = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }

        // Filter out empty values
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '' && $value !== false;
        });

        if (!empty($filters['onlyMyAdverts']) && $this->getUser()) {
            $filters['user'] = $this->getUser(); // used by AdvertRepository
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
    #[Route('/advert/view/{id}', name: 'app_advert_view')]
    public function view(int $id, AdvertRepository $advertRepository): Response
    {
        $advert = $advertRepository->find($id);

        if (!$advert) {
            $this->addFlash('error', 'Advert not found.');
            return $this->redirectToRoute('app_adverts_list');
        }

        return $this->render('adverts/advert_view.html.twig', [
            'advert' => $advert,
        ]);
    }


    #[Route('/advert/create', name: 'app_advert_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $advert = new Adverts();
        $form = $this->createForm(AdvertCreateEditFormType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advert->setUser($this->getUser());

            // Handle image uploads (up to 4 images)
            $newImages = $form->get('images')->getData();
            if (!empty($newImages)) {
                foreach ($newImages as $image) {
                    $filename = $this->fileUploadService->uploadImage($image);
                    $advertImage = new AdvertImage();
                    $advertImage->setPath($filename);
                    $advert->addAdvertImage($advertImage);
                }
            }

            $entityManager->persist($advert);
            $entityManager->flush();

            $this->addFlash('success', 'Advert created successfully.');
            return $this->redirectToRoute('app_adverts_list');
        }

        return $this->render('adverts/advert_create_edit.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/advert/edit/{id}', name: 'app_advert_edit')]
    public function edit(
        int $id,
        Request $request,
        AdvertRepository $advertRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $advert = $advertRepository->find($id);
        if (!$advert) {
            $this->addFlash('error', 'Advert not found.');
            return $this->redirectToRoute('app_adverts_list');
        }

        if ($advert->getUser() !== $this->getUser() && !$this->isGranted('ROLE_MODERATOR')) {
            $this->addFlash('error', 'You do not have permission to edit this advert.');
            return $this->redirectToRoute('app_adverts_list');
        }

        $form = $this->createForm(AdvertCreateEditFormType::class, $advert, ['isEdit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newImages = $form->get('images')->getData();

            if (!empty($newImages)) {
                // Delete all existing images and their entities
                $existingImages = $advert->getAdvertImages();
                foreach ($existingImages as $image) {
                    $this->fileUploadService->deleteImage($image->getPath());
                    $advert->removeAdvertImage($image);
                }

                // Upload and associate new images
                foreach ($newImages as $image) {
                    $filename = $this->fileUploadService->uploadImage($image);
                    $advertImage = new AdvertImage();
                    $advertImage->setPath($filename);
                    $advert->addAdvertImage($advertImage);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Advert updated successfully.');
            return $this->redirectToRoute('app_adverts_list');
        }

        return $this->render('adverts/advert_create_edit.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
    }


    #[Route('/advert/delete/{id}', name: 'app_advert_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        AdvertRepository $advertRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        $submittedToken = $request->request->get('_token');

        // Check if the token is valid
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete' . $id, $submittedToken))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $advert = $advertRepository->find($id);
        if (!$advert) {
            $this->addFlash('error', 'Advert not found.');
            return $this->redirectToRoute('app_adverts_list');
        }

        // Delete all associated images from the filesystem
        $existingImages = $advert->getAdvertImages();
        $imagePaths = array_map(fn($image) => $image->getPath(), $existingImages->toArray());
        $this->fileUploadService->deleteImages($imagePaths);

        $entityManager->remove($advert);
        $entityManager->flush();

        $this->addFlash('success', 'Advert deleted successfully.');
        return $this->redirectToRoute('app_adverts_list');
    }
}
