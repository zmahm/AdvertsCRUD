<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryCreateEditFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    //creates category
    #[Route('/category/create', name: 'app_category_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Restrict access to admins

        $category = new Category();
        $form = $this->createForm(CategoryCreateEditFormType::class, $category, [
            'isEdit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category created successfully.');
            return $this->redirectToRoute('app_category_list');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('category/category_create_edit.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/categories', name: 'app_category_list')]
    public function list(CategoryRepository $categoryRepository, Request $request): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('category/category_list.html.twig', [
            'categories' => $categories,
        ]);
    }

    //edits category by ID
    #[Route('/category/edit/{id}', name: 'app_category_edit')]
    public function edit(
        int $id,
        Request $request,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Restrict access to admins

        $category = $categoryRepository->find($id);

        if (!$category) {
            $this->addFlash('error', 'Category not found.');
            return $this->redirectToRoute('app_category_list');
        }

        $form = $this->createForm(CategoryCreateEditFormType::class, $category, [
            'isEdit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Category updated successfully.');
            return $this->redirectToRoute('app_category_list');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('category/category_create_edit.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
    }

    // Deletes a category
    #[Route('/category/delete/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Restrict access to admins

        // Fetch the category
        $category = $categoryRepository->find($id);

        if (!$category) {
            $this->addFlash('error', 'Category not found.');
            return $this->redirectToRoute('app_category_list');
        }

        // CSRF token validation
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $id, $submittedToken)) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_category_list');
        }

        // Remove the category
        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', 'Category deleted successfully.');
        return $this->redirectToRoute('app_category_list');
    }


}
