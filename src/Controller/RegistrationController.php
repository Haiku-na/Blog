<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{

    // Liste des utilisateurs
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users', name: 'app_users')]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        
        return $this->render('registration/usersList.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}/comments', name: 'app_user_comments', requirements: ['id' => '\d+'])]
    public function userComments(int $id, CommentRepository $commentRepository, UserRepository $userRepository): Response
    {
    $user = $userRepository->find($id);
    
    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    $comments = $commentRepository->findBy(['user' => $user]);

    return $this->render('registration/user_comments.html.twig', [
        'user' => $user, // Ajoute ici l'utilisateur à la vue
        'comments' => $comments,
    ]);
    }

    #[Route('/user/{id}/update_role', name: 'app_user_update_role', methods: ['POST'])]
    public function updateRole(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur par son ID
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Récupérer le rôle sélectionné depuis la requête POST
        $role = $request->request->get('role');

        if ($role) {
            // Ajoute le rôle au tableau des rôles de l'utilisateur
            $user->setRoles([$role]);

            // Sauvegarder dans la base de données
            $entityManager->flush();

            $this->addFlash('success', 'Rôle de l\'utilisateur mis à jour.');
        } else {
            $this->addFlash('error', 'Aucun rôle sélectionné.');
        }

        // Rediriger vers la liste des utilisateurs
        return $this->redirectToRoute('app_users');
    }
    
    #[Route('/user/{id}/posts', name: 'app_user_posts')]
    public function userPosts(int $id, UserRepository $userRepository, PostRepository $postRepository): Response
{
    $user = $userRepository->find($id);

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé.');
    }

    // Récupérer les posts associés à cet utilisateur
    $posts = $postRepository->findBy(['user' => $user]);

    return $this->render('registration/user_posts.html.twig', [
        'user' => $user,
        'posts' => $posts,
    ]);
}


    // Afficher une catégorie spécifique
    #[IsGranted('ROLE_USER')]
    #[Route('/user/{id}', name: 'app_user', requirements: ['id' => '\d+'])]
    public function show(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Pas d utilisateur demandée n\'existe pas.');
        }

        return $this->render('register/compte.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $form->get('profilePicture')->getData();

            if ($file) {
                // Si un fichier est téléchargé, gérer le fichier
                $newFilename = uniqid() . '.' . $file->guessExtension();  // Générer un nom unique

                // Déplacer le fichier
                $file->move(
                    $this->getParameter('uploads_directory'),  // Assure-toi d'avoir configuré ce paramètre
                    $newFilename
                );

                // Assigner le nom du fichier à l'entité
                $user->setProfilePicture($newFilename);
            } else {    
                // Si aucun fichier n'est téléchargé, ne rien changer, picture restera à null
                $user->setProfilePicture(null);
            }   


            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_posts');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
