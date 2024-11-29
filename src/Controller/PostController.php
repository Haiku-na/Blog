<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;

use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Entity\Post;
use App\Form\PostType;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_posts')]
    public function list(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/post/{id}', name: 'app_post', requirements: ['id' => '\d+'])]
    public function show(int $id, PostRepository $postRepository, CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('La publication demandée n\'existe pas.');
        }

        $comments = $commentRepository->findBy(['post' => $post], ['createAt' => 'DESC']);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        // Associer le commentaire au post et à l'utilisateur connecté
            $comment->setPost($post);
            $comment->setUser($this->getUser()); // Si tu as une fonctionnalité d'utilisateur connecté

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté.');

            return $this->redirectToRoute('app_post', ['id' => $post->getId()]);
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $comments, // Ici tu passes la variable comments
            'commentForm' => $form->createView(),
        ]);


        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/post_add', name: 'app_post_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $form->get('picture')->getData();

            if ($file) {
                // Si un fichier est téléchargé, gérer le fichier
                $newFilename = uniqid() . '.' . $file->guessExtension();  // Générer un nom unique

                // Déplacer le fichier
                $file->move(
                    $this->getParameter('uploads_directory'),  // Assure-toi d'avoir configuré ce paramètre
                    $newFilename
                );

                // Assigner le nom du fichier à l'entité
                $post->setPicture($newFilename);
            } else {    
                // Si aucun fichier n'est téléchargé, ne rien changer, picture restera à null
                $post->setPicture(null);
            }   


            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Publication ajoutée avec succès.');
            return $this->redirectToRoute('app_posts');
        }

        return $this->render('post/postForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/post/edit/{id}', name: 'app_post_edit')]
    public function edit(int $id, PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException("Le post avec l'ID $id n'existe pas.");
        }

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $form->get('picture')->getData();

            if ($file) {
                // Si un fichier est téléchargé, gérer le fichier
                $newFilename = uniqid() . '.' . $file->guessExtension();  // Générer un nom unique

                // Déplacer le fichier
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                // Assigner le nom du fichier à l'entité
                $post->setPicture($newFilename);
            } else {
                // Si aucun fichier n'est téléchargé, laisser `picture` à `null`
                $post->setPicture(null);
            }

            $entityManager->flush();


            return $this->redirectToRoute('app_posts');
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }




    #[IsGranted('ROLE_ADMIN')]
#[Route('/post/delete/{id}', name: 'app_post_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
public function delete(int $id, Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository, CommentRepository $commentRepository): Response
{
    // Recherche du post à supprimer
    $post = $postRepository->find($id);

    if (!$post) {
        throw $this->createNotFoundException('La publication demandée n\'existe pas.');
    }

    // Récupérer les commentaires associés à ce post
    $comments = $commentRepository->findBy(['post' => $post]);

    // Supprimer tous les commentaires associés
    foreach ($comments as $comment) {
        $entityManager->remove($comment);
    }

    // Vérifier et valider le token CSRF avant de supprimer
    if ($this->isCsrfTokenValid('delete_post_' . $post->getId(), $request->request->get('_token'))) {
        // Supprimer le post
        $entityManager->remove($post);
        $entityManager->flush();  // Appliquer les suppressions dans la base de données

        // Message flash pour indiquer que la suppression a réussi
        $this->addFlash('success', 'Publication et commentaires supprimés avec succès.');
    }

    // Rediriger vers la liste des posts
    return $this->redirectToRoute('app_posts');
}
}
