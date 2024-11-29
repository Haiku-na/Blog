<?php
namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CategoryController extends AbstractController
{
    // Liste des catégories
    #[Route('/categories', name: 'app_categories')]
    public function list(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        
        return $this->render('category/list.html.twig', [
            'categories' => $categories,
        ]);
    }

    // Afficher une catégorie spécifique
    #[IsGranted('ROLE_USER')]
    #[Route('/category/{id}', name: 'app_category', requirements: ['id' => '\d+'])]
    public function show(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
        }

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    // Ajouter une nouvelle catégorie
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/add', name: 'app_category_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie ajoutée avec succès.');

            return $this->redirectToRoute('app_categories');
        }

        return $this->render('category/categoryForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Modifier une catégorie existante
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/edit/{id}', name: 'app_category_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie mise à jour avec succès.');

            return $this->redirectToRoute('app_categories');
        }

        return $this->render('category/categoryForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Supprimer une catégorie
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/delete/{id}', name: 'app_category_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('delete_category_' . $category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès.');
        }

        return $this->redirectToRoute('app_categories');
    }
}

?>