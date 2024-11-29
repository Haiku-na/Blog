<?php

// src/Controller/CommentController.php

namespace App\Controller;

use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{

    #[Route('/comments', name: 'app_all_comments')]
    public function listComments(CommentRepository $commentRepository): Response
    {
    // Récupère tous les commentaires
    $comments = $commentRepository->findAll();

    // Si aucun commentaire n'est trouvé, on passe un tableau vide
    if (!$comments) {
        $comments = [];
    }

    // Rend la vue avec les commentaires
    return $this->render('comment/commentsList.html.twig', [
        'comments' => $comments,
    ]);}

    #[Route('/comment/{id}/update_status', name: 'comment_update_status', methods: ['POST'])]
public function updateStatus(int $id, Request $request, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
{
    $comment = $commentRepository->find($id);

    if (!$comment) {
        throw $this->createNotFoundException('Le commentaire n\'existe pas.');
    }

    // Mise à jour du statut
    $newStatus = $request->request->get('status');
    $comment->setStatus($newStatus);

    $entityManager->flush();

    // Redirection vers le post associé au commentaire
    return $this->redirectToRoute('app_all_comments');
}

    // #[Route('/comments/{postId}', name: 'app_comments')]
    // public function list(int $postId, PostRepository $postRepository, CommentRepository $commentRepository): Response
    // {
    //     $post = $postRepository->find($postId);

    //     if (!$post) {
    //         throw $this->createNotFoundException('Le post n\'existe pas.');
    //     }

    //     $comments = $commentRepository->findBy(['post' => $post]);

    //     return $this->render('comment/index.html.twig', [
    //         'post' => $post,
    //         'comments' => $comments,
    //     ]);
    // }

    #[Route('/comment/{id}', name: 'app_comment', requirements: ['id' => '\d+'])]
    public function show(int $id, CommentRepository $commentRepository): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Le commentaire demandé n\'existe pas.');
        }

        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/comment_add/{postId}', name: 'app_comment_add')]
    public function add(int $postId, Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
    {
        $post = $postRepository->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas.');
        }

        $comment = new Comment();
        $comment->setPost($post);

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
            return $this->redirectToRoute('app_comments', ['postId' => $postId]);
        }

        return $this->render('comment/commentForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/comment/edit/{id}', name: 'app_comment_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Le commentaire n\'existe pas.');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire mis à jour avec succès.');
            return $this->redirectToRoute('app_comments', ['postId' => $comment->getPost()->getId()]);
        }

        return $this->render('comment/commentForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/comment/delete/{id}', name: 'app_comment_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Le commentaire n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('delete_comment_' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire supprimé avec succès.');
        }

        return $this->redirectToRoute('app_comments', ['postId' => $comment->getPost()->getId()]);
    }
}
